@php
    $senderAddressLines = $confirmation->senderAddressLines();
    $logo = $confirmation->senderCompanyLogoDataUri();
@endphp

<article class="card public-document">
    <div class="public-document-header">
        <div>
            <p class="page-eyebrow">Referentie</p>
            <h2>{{ $confirmation->reference }}</h2>
        </div>
        @if ($logo !== null)
            <img class="public-document-logo" src="{{ $logo }}" alt="">
        @endif
        <span class="dashboard-status dashboard-status-{{ $confirmation->status }}">{{ ucfirst($confirmation->status) }}</span>
    </div>

    <div class="public-document-grid">
        <div>
            <p><strong>Opdrachtnemer</strong></p>
            <p>{{ $confirmation->senderCompanyDisplayName() }}</p>
            @foreach ($senderAddressLines as $line)
                <p>{{ $line }}</p>
            @endforeach
            @if (filled($confirmation->sender_kvk_number))
                <p>KVK: {{ $confirmation->sender_kvk_number }}</p>
            @endif
        </div>
        <div>
            <p><strong>Opdrachtgever</strong></p>
            <p>{{ $confirmation->client_name }}</p>
            <p>{{ $confirmation->client_contact_name ?: '-' }}</p>
            <p>{{ $confirmation->client_email }}</p>
        </div>
    </div>

    <div class="public-document-grid">
        <div>
            <p><strong>Vergoeding</strong></p>
            <p>EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }} ({{ $confirmation->valueVatLabel() }})</p>
        </div>
        <div>
            <p><strong>Startdatum</strong></p>
            <p>{{ optional($confirmation->agreement_date)->format('d-m-Y') ?? 'Niet ingevuld' }}</p>
        </div>
    </div>

    @if (filled($confirmation->duration))
        <div class="public-document-grid">
            <div>
                <p><strong>Duur van de opdracht</strong></p>
                <p>{{ $confirmation->duration }}</p>
            </div>
        </div>
    @endif

    <div class="public-document-body">
        <h3>Omschrijving</h3>
        <div class="dashboard-rich-content">{!! $confirmation->descriptionHtml() !!}</div>
    </div>

    @if ($confirmation->hasSpecifications())
        <div class="public-document-body">
            <h3>Aanvullende specificaties</h3>
            @include('partials.confirmations.specifications', ['confirmation' => $confirmation])
        </div>
    @endif

    @if ($confirmation->defaultAgreementsHtml() !== '')
        <div class="public-document-body">
            <h3>Basis afspraken</h3>
            <div class="dashboard-rich-content">{!! $confirmation->defaultAgreementsHtml() !!}</div>
        </div>
    @endif

    @if ($confirmation->terms_path)
        <div class="public-document-body">
            <h3>Algemene voorwaarden</h3>
            <p>{{ $confirmation->terms_original_name ?: basename($confirmation->terms_path) }} is meegestuurd als bijlage en is van toepassing op deze opdracht.</p>
        </div>
    @endif
</article>
