@extends('layouts.dashboard', [
    'title' => 'Abonnement',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Abonnement',
        'title' => 'Kies je abonnement',
        'text' => 'Je kunt 14 dagen starten zonder betaalgegevens. Daarna kies je maandelijks of jaarlijks en verloopt de betaling via Mollie.',
    ])

    @include('partials.forms.status')

    @unless ($mollieConfigured)
        <div class="dashboard-notice dashboard-notice-warning">
            Mollie is nog niet ingesteld. Voeg eerst de Mollie API key toe voordat echte betalingen gestart kunnen worden.
        </div>
    @endunless

    <section class="dashboard-panel dashboard-panel-wide billing-status-panel">
        <h2>Status</h2>

        @if (auth()->user()->hasActiveSubscription())
            <p>Je abonnement is actief{{ auth()->user()->subscriptionPlanName() ? ': '.auth()->user()->subscriptionPlanName() : '' }}.</p>
            @if (auth()->user()->subscription_renews_at)
                <p>Volgende verlenging: <strong>{{ auth()->user()->subscription_renews_at->format('d-m-Y') }}</strong>.</p>
            @endif
        @elseif (auth()->user()->hasPendingSubscription())
            <p>Je betaling is gestart. We wachten nog op bevestiging van Mollie.</p>
        @elseif (auth()->user()->isOnTrial())
            <p>Je gratis proefperiode loopt nog <strong>{{ auth()->user()->trialDaysRemaining() }} dagen</strong>, tot {{ auth()->user()->trial_ends_at->format('d-m-Y') }}.</p>
        @else
            <p>Je gratis proefperiode is afgelopen. Kies een abonnement om verder te gaan.</p>
        @endif
    </section>

    <div class="billing-plan-grid">
        @foreach ($plans as $key => $plan)
            <article class="billing-plan-card{{ $key === 'yearly' ? ' billing-plan-card-featured' : '' }}">
                @if ($key === 'yearly')
                    <p class="billing-plan-badge">Meeste voordeel</p>
                @endif

                <h2>{{ $plan['name'] }}</h2>
                <p class="billing-plan-price">
                    <strong>€{{ number_format((float) $plan['amount_ex_vat'], 2, ',', '.') }}</strong>
                    <span>excl. {{ $vatRate }}% btw per {{ $plan['period'] === 'year' ? 'jaar' : 'maand' }}</span>
                </p>

                <ul class="billing-plan-features">
                    <li>Onbeperkt opdrachtbevestigingen versturen</li>
                    <li>Incl. Kamer van Koophandel API</li>
                    <li>Digitale akkoordflow en PDF-generatie</li>
                    <li>Automatische verlenging via Mollie</li>
                </ul>

                <form method="POST" action="{{ route('billing.checkout', $key) }}">
                    @csrf
                    <button
                        type="submit"
                        class="btn {{ $key === 'yearly' ? 'btn-primary' : 'btn-secondary' }}"
                        @disabled(! $mollieConfigured || auth()->user()->hasActiveSubscription() || auth()->user()->hasPendingSubscription())
                    >
                        {{ $plan['cta'] }}
                    </button>
                </form>
            </article>
        @endforeach
    </div>
@endsection
