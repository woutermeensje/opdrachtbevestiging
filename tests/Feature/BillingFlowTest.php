<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BillingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_expired_trial_redirects_dashboard_to_billing_page(): void
    {
        $user = User::factory()->create([
            'trial_ends_at' => now()->subDay(),
            'subscription_status' => 'trialing',
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('billing.show'));

        $this
            ->actingAs($user)
            ->get(route('billing.show'))
            ->assertOk()
            ->assertSee('Kies je abonnement')
            ->assertSee('Je gratis proefperiode is afgelopen');
    }

    public function test_active_subscription_can_access_dashboard_after_trial(): void
    {
        $user = User::factory()->create([
            'trial_ends_at' => now()->subDay(),
            'subscription_status' => 'active',
            'subscription_plan' => 'monthly',
            'subscription_started_at' => now()->subMonth(),
            'subscription_renews_at' => now()->addMonth(),
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_checkout_creates_mollie_customer_and_first_payment(): void
    {
        config()->set('billing.mollie_key', 'test_mollie_key');

        Http::fake([
            'https://api.mollie.com/v2/customers' => Http::response([
                'id' => 'cst_123',
            ], 201),
            'https://api.mollie.com/v2/customers/cst_123/payments' => Http::response([
                'id' => 'tr_123',
                '_links' => [
                    'checkout' => [
                        'href' => 'https://www.mollie.com/checkout/test',
                    ],
                ],
            ], 201),
        ]);

        $user = User::factory()->create([
            'trial_ends_at' => now()->subDay(),
            'subscription_status' => 'trialing',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('billing.checkout', 'monthly'));

        $response->assertRedirect('https://www.mollie.com/checkout/test');

        $user->refresh();
        $this->assertSame('pending', $user->subscription_status);
        $this->assertSame('monthly', $user->pending_subscription_plan);
        $this->assertSame('cst_123', $user->mollie_customer_id);
        $this->assertSame('tr_123', $user->mollie_pending_payment_id);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.mollie.com/v2/customers/cst_123/payments'
                && $request['sequenceType'] === 'first'
                && $request['amount']['value'] === '24.14'
                && $request['metadata']['plan'] === 'monthly';
        });
    }

    public function test_paid_first_payment_activates_subscription_from_webhook(): void
    {
        config()->set('billing.mollie_key', 'test_mollie_key');

        $user = User::factory()->create([
            'trial_ends_at' => now()->subDay(),
            'subscription_status' => 'pending',
            'pending_subscription_plan' => 'monthly',
            'mollie_customer_id' => 'cst_123',
            'mollie_pending_payment_id' => 'tr_123',
        ]);

        Http::fake([
            'https://api.mollie.com/v2/payments/tr_123' => Http::response([
                'id' => 'tr_123',
                'status' => 'paid',
                'sequenceType' => 'first',
                'customerId' => 'cst_123',
                'mandateId' => 'mdt_123',
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => 'monthly',
                ],
            ]),
            'https://api.mollie.com/v2/customers/cst_123/subscriptions' => Http::response([
                'id' => 'sub_123',
            ], 201),
        ]);

        $this
            ->post(route('billing.webhook'), ['id' => 'tr_123'])
            ->assertOk();

        $user->refresh();
        $this->assertSame('active', $user->subscription_status);
        $this->assertSame('monthly', $user->subscription_plan);
        $this->assertSame('mdt_123', $user->mollie_mandate_id);
        $this->assertSame('sub_123', $user->mollie_subscription_id);
        $this->assertNull($user->mollie_pending_payment_id);
        $this->assertNull($user->pending_subscription_plan);
        $this->assertNotNull($user->subscription_renews_at);
    }

    public function test_active_subscription_cannot_start_second_checkout(): void
    {
        config()->set('billing.mollie_key', 'test_mollie_key');
        Http::fake();

        $user = User::factory()->create([
            'subscription_status' => 'active',
            'subscription_plan' => 'monthly',
        ]);

        $this
            ->actingAs($user)
            ->post(route('billing.checkout', 'yearly'))
            ->assertRedirect(route('billing.show'))
            ->assertSessionHas('status', 'Je abonnement is al actief.');

        Http::assertNothingSent();
    }
}
