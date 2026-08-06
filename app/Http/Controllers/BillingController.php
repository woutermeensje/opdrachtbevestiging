<?php

namespace App\Http\Controllers;

use App\Services\MollieBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class BillingController extends Controller
{
    public function __construct(
        private readonly MollieBillingService $billing,
    ) {}

    public function show(Request $request): View
    {
        return view('dashboard.billing', [
            'plans' => config('billing.plans'),
            'mollieConfigured' => $this->billing->isConfigured(),
            'vatRate' => config('billing.vat_rate'),
        ]);
    }

    public function checkout(Request $request, string $plan): RedirectResponse
    {
        if ($request->user()->hasActiveSubscription()) {
            return redirect()
                ->route('billing.show')
                ->with('status', 'Je abonnement is al actief.');
        }

        if ($request->user()->hasPendingSubscription() && filled($request->user()->mollie_pending_payment_id)) {
            return redirect()
                ->route('billing.show')
                ->with('status', 'Er staat al een betaling klaar bij Mollie. Rond die betaling af of wacht op de bevestiging.');
        }

        try {
            $checkoutUrl = $this->billing->startCheckout($request->user(), $plan);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('billing.show')
                ->with('status', 'De betaalpagina kon niet worden gestart. Controleer de Mollie-instellingen en probeer het opnieuw.');
        }

        return redirect()->away($checkoutUrl);
    }

    public function return(Request $request): RedirectResponse
    {
        try {
            $user = $this->billing->syncPendingPaymentFor($request->user());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('billing.show')
                ->with('status', 'We wachten nog op de bevestiging van Mollie. Ververs deze pagina over een moment.');
        }

        if ($user->hasActiveSubscription()) {
            return redirect()
                ->route('dashboard')
                ->with('status', 'Je abonnement is actief. Je kunt weer verder met je opdrachtbevestigingen.');
        }

        return redirect()
            ->route('billing.show')
            ->with('status', 'We hebben je betaling nog niet bevestigd gekregen. Zodra Mollie bevestigt, wordt je abonnement automatisch actief.');
    }

    public function cancelled(): RedirectResponse
    {
        return redirect()
            ->route('billing.show')
            ->with('status', 'De betaling is geannuleerd. Je kunt opnieuw een abonnement kiezen wanneer je wilt.');
    }

    public function webhook(Request $request): Response
    {
        $paymentId = $request->input('id');

        if (! is_string($paymentId) || $paymentId === '') {
            return response('Missing payment id.', 400);
        }

        try {
            $this->billing->syncPayment($paymentId);
        } catch (Throwable $exception) {
            report($exception);

            return response('Webhook could not be processed.', 500);
        }

        return response('OK');
    }
}
