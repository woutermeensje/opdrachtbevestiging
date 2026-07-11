@php
    $recipientName = $confirmation->client_contact_name ?: $confirmation->client_name;
    $senderCompany = $confirmation->senderCompanyDisplayName();
    $senderAddressLines = $confirmation->senderAddressLines();
    $totalValue = (float) $confirmation->total_value;
    $emailAttachmentSummary = $confirmation->emailAttachmentSummary();
    $acceptUrl = $confirmation->publicUrl();
@endphp
Opdrachtbevestiging {{ $confirmation->reference }}: {{ $confirmation->title }}

Beste {{ $recipientName }},

Hierbij bevestigen wij de opdracht zoals hieronder uitgewerkt. Deze e-mail bevat de volledige opdrachtbevestiging @if ($emailAttachmentSummary !== ''). Bijgevoegd: {{ $emailAttachmentSummary }}@endif.

Akkoord geven:
{{ $acceptUrl }}

Referentie: {{ $confirmation->reference }}
Titel: {{ $confirmation->title }}
@if ($confirmation->agreement_date !== null)
Startdatum: {{ $confirmation->agreement_date->format('d-m-Y') }}
@endif
@if (filled($confirmation->duration))
Duur van de opdracht: {{ $confirmation->duration }}
@endif
@if ($totalValue > 0)
Vergoeding: EUR {{ number_format($totalValue, 2, ',', '.') }} ({{ $confirmation->valueVatLabel() }})
@endif
@if ($confirmation->expires_at !== null)
Vervaldatum: {{ $confirmation->expires_at->format('d-m-Y') }}
@endif

Opdrachtgever
{{ $confirmation->client_name }}
{{ $confirmation->client_contact_name ?: '-' }}
{{ $confirmation->client_email }}
@if (filled($confirmation->client_kvk_number))
KVK: {{ $confirmation->client_kvk_number }}
@endif

Opdrachtnemer
{{ $senderCompany }}
@foreach ($senderAddressLines as $line)
{{ $line }}
@endforeach
@if (filled($confirmation->sender_kvk_number))
KVK: {{ $confirmation->sender_kvk_number }}
@endif
{{ $confirmation->sender_name ?: '-' }}
{{ $confirmation->sender_email ?: '-' }}

Omschrijving van de opdracht
{{ $confirmation->descriptionText() }}

@if ($confirmation->defaultAgreementsText() !== '')
Basis afspraken
{{ $confirmation->defaultAgreementsText() }}
@endif

@if ($confirmation->terminationTermsText() !== '')
Beëindiging van de opdracht
{{ $confirmation->terminationTermsText() }}
@endif

@if ($confirmation->terms_path)
Op deze opdracht zijn de bijgevoegde algemene voorwaarden van toepassing.
@endif

Met vriendelijke groet,
{{ $confirmation->sender_name ?: $senderCompany }}
{{ $senderCompany }}
