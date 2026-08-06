@php
    $user = auth()->user();
@endphp

@if ($user && ! request()->routeIs('billing.*') && ! $user->hasActiveSubscription())
    @if ($user->isOnTrial())
        <div class="dashboard-billing-banner">
            <div>
                <strong>{{ $user->trialDaysRemaining() }} dagen gratis over</strong>
                <span>Je hoeft pas na je proefperiode betaalgegevens in te vullen.</span>
            </div>
            <a href="{{ route('billing.show') }}" class="btn btn-secondary btn-small">Abonnement bekijken</a>
        </div>
    @elseif ($user->hasPendingSubscription())
        <div class="dashboard-billing-banner dashboard-billing-banner-warning">
            <div>
                <strong>Betaling in behandeling</strong>
                <span>Zodra Mollie bevestigt, wordt je abonnement automatisch actief.</span>
            </div>
            <a href="{{ route('billing.show') }}" class="btn btn-secondary btn-small">Status bekijken</a>
        </div>
    @endif
@endif
