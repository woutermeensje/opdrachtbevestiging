<?php

namespace App\Services;

use App\Exceptions\MollieBillingException;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class MollieBillingService
{
    public function isConfigured(): bool
    {
        return filled(config('billing.mollie_key'));
    }

    /**
     * @return array<string, mixed>
     */
    public function plan(string $planKey): array
    {
        $plan = config('billing.plans.'.$planKey);

        if (! is_array($plan)) {
            throw new InvalidArgumentException('Onbekend abonnement.');
        }

        return $plan;
    }

    public function startCheckout(User $user, string $planKey): string
    {
        $this->ensureConfigured();

        $plan = $this->plan($planKey);
        $customerId = $this->ensureCustomer($user);

        $payment = $this->client()
            ->post("/customers/{$customerId}/payments", [
                'amount' => $this->amountPayload($plan),
                'description' => $plan['description'],
                'redirectUrl' => route('billing.return'),
                'cancelUrl' => route('billing.cancelled'),
                'webhookUrl' => route('billing.webhook'),
                'sequenceType' => 'first',
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => $planKey,
                    'source' => 'subscription_first_payment',
                ],
            ])
            ->throw()
            ->json();

        $checkoutUrl = data_get($payment, '_links.checkout.href');

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new MollieBillingException('Mollie gaf geen checkout-link terug.');
        }

        $user->forceFill([
            'subscription_status' => 'pending',
            'pending_subscription_plan' => $planKey,
            'mollie_pending_payment_id' => data_get($payment, 'id'),
        ])->save();

        return $checkoutUrl;
    }

    public function syncPayment(string $paymentId): ?User
    {
        $this->ensureConfigured();

        $payment = $this->client()
            ->get("/payments/{$paymentId}")
            ->throw()
            ->json();

        $user = $this->findUserForPayment($payment);

        if (! $user) {
            return null;
        }

        if (($payment['sequenceType'] ?? null) === 'first' || $user->mollie_pending_payment_id === $paymentId) {
            return $this->syncFirstPayment($user, $payment);
        }

        return $this->syncRecurringPayment($user, $payment);
    }

    public function syncPendingPaymentFor(User $user): User
    {
        if (! filled($user->mollie_pending_payment_id)) {
            return $user;
        }

        return $this->syncPayment($user->mollie_pending_payment_id) ?? $user;
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new MollieBillingException('Mollie is nog niet ingesteld.');
        }
    }

    private function ensureCustomer(User $user): string
    {
        if (filled($user->mollie_customer_id)) {
            return $user->mollie_customer_id;
        }

        $customer = $this->client()
            ->post('/customers', [
                'name' => $user->name,
                'email' => $user->email,
                'locale' => 'nl_NL',
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ])
            ->throw()
            ->json();

        $customerId = data_get($customer, 'id');

        if (! is_string($customerId) || $customerId === '') {
            throw new MollieBillingException('Mollie gaf geen klantnummer terug.');
        }

        $user->forceFill([
            'mollie_customer_id' => $customerId,
        ])->save();

        return $customerId;
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    private function syncFirstPayment(User $user, array $payment): User
    {
        $status = $payment['status'] ?? null;
        $paymentId = $payment['id'] ?? null;

        if (in_array($status, ['paid', 'authorized'], true)) {
            $planKey = $this->planKeyFromPayment($user, $payment);
            $plan = $this->plan($planKey);

            if (! filled($user->mollie_subscription_id)) {
                $subscription = $this->client()
                    ->post("/customers/{$user->mollie_customer_id}/subscriptions", [
                        'amount' => $this->amountPayload($plan),
                        'interval' => $plan['interval'],
                        'startDate' => $this->nextRenewalDate($planKey)->toDateString(),
                        'description' => $plan['description'].' gebruiker '.$user->id,
                        'webhookUrl' => route('billing.webhook'),
                        'metadata' => [
                            'user_id' => $user->id,
                            'plan' => $planKey,
                            'source' => 'subscription_recurring_payment',
                        ],
                    ])
                    ->throw()
                    ->json();

                $user->mollie_subscription_id = data_get($subscription, 'id');
            }

            $user->forceFill([
                'subscription_status' => 'active',
                'subscription_plan' => $planKey,
                'subscription_started_at' => now(),
                'subscription_renews_at' => $this->nextRenewalDate($planKey),
                'mollie_mandate_id' => data_get($payment, 'mandateId', $user->mollie_mandate_id),
                'mollie_pending_payment_id' => null,
                'pending_subscription_plan' => null,
            ])->save();

            return $user->refresh();
        }

        if (in_array($status, ['canceled', 'expired', 'failed'], true) && $user->mollie_pending_payment_id === $paymentId) {
            $user->forceFill([
                'subscription_status' => $user->isOnTrial() ? 'trialing' : 'payment_failed',
                'mollie_pending_payment_id' => null,
                'pending_subscription_plan' => null,
            ])->save();
        }

        return $user->refresh();
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    private function syncRecurringPayment(User $user, array $payment): User
    {
        $status = $payment['status'] ?? null;
        $planKey = $this->planKeyFromPayment($user, $payment);

        if (in_array($status, ['paid', 'authorized'], true)) {
            $paidAt = data_get($payment, 'paidAt');
            $baseDate = is_string($paidAt) ? Carbon::parse($paidAt) : now();

            $user->forceFill([
                'subscription_status' => 'active',
                'subscription_plan' => $planKey,
                'subscription_renews_at' => $this->nextRenewalDate($planKey, $baseDate),
            ])->save();
        }

        if (in_array($status, ['canceled', 'expired', 'failed'], true)) {
            $user->forceFill([
                'subscription_status' => 'past_due',
            ])->save();
        }

        return $user->refresh();
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    private function findUserForPayment(array $payment): ?User
    {
        $metadataUserId = data_get($payment, 'metadata.user_id');

        if (filled($metadataUserId)) {
            return User::query()->find($metadataUserId);
        }

        $paymentId = data_get($payment, 'id');

        if (filled($paymentId)) {
            $user = User::query()->where('mollie_pending_payment_id', $paymentId)->first();

            if ($user) {
                return $user;
            }
        }

        $subscriptionId = data_get($payment, 'subscriptionId');

        if (filled($subscriptionId)) {
            $user = User::query()->where('mollie_subscription_id', $subscriptionId)->first();

            if ($user) {
                return $user;
            }
        }

        $customerId = data_get($payment, 'customerId');

        if (filled($customerId)) {
            return User::query()->where('mollie_customer_id', $customerId)->first();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    private function planKeyFromPayment(User $user, array $payment): string
    {
        $planKey = data_get($payment, 'metadata.plan')
            ?? $user->pending_subscription_plan
            ?? $user->subscription_plan;

        if (! is_string($planKey) || ! is_array(config('billing.plans.'.$planKey))) {
            throw new MollieBillingException('Mollie-betaling heeft geen geldig abonnement.');
        }

        return $planKey;
    }

    /**
     * @param  array<string, mixed>  $plan
     * @return array{currency: string, value: string}
     */
    private function amountPayload(array $plan): array
    {
        return [
            'currency' => config('billing.currency', 'EUR'),
            'value' => number_format($this->amountIncludingVat($plan), 2, '.', ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function amountIncludingVat(array $plan): float
    {
        $amountExVat = (float) $plan['amount_ex_vat'];
        $vatRate = (float) config('billing.vat_rate', 21);

        return round($amountExVat * (1 + ($vatRate / 100)), 2);
    }

    private function nextRenewalDate(string $planKey, ?Carbon $baseDate = null): Carbon
    {
        $date = ($baseDate ?: now())->copy();

        return match ($this->plan($planKey)['period']) {
            'year' => $date->addYearNoOverflow(),
            default => $date->addMonthNoOverflow(),
        };
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl('https://api.mollie.com/v2')
            ->withToken(config('billing.mollie_key'))
            ->acceptJson()
            ->asJson();
    }
}
