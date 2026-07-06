@php
    $recipientName = $confirmation->client_contact_name ?: $confirmation->client_name;
    $senderCompany = $confirmation->user->company_name ?: $confirmation->sender_name;
@endphp
Opdrachtbevestiging {{ $confirmation->reference }}: {{ $confirmation->title }}

Beste {{ $recipientName }},

Hierbij bevestigen wij de opdracht zoals hieronder uitgewerkt. Deze e-mail bevat de volledige opdrachtbevestiging; er is geen bijlage toegevoegd.

Je kunt rechtstreeks op deze e-mail reageren bij vragen of om akkoord te geven.

Opdrachtnemer
{{ $senderCompany }}
{{ $confirmation->sender_name ?: '-' }}
{{ $confirmation->sender_email ?: '-' }}

Opdrachtgever
{{ $confirmation->client_name }}
{{ $confirmation->client_contact_name ?: '-' }}
{{ $confirmation->client_email }}

Referentie: {{ $confirmation->reference }}
@if ($confirmation->agreement_date !== null)
Opdrachtdatum: {{ $confirmation->agreement_date->format('d-m-Y') }}
@endif
@if ((float) $confirmation->total_value > 0)
Waarde: EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }}
@endif
@if ($confirmation->expires_at !== null)
Vervaldatum: {{ $confirmation->expires_at->format('d-m-Y') }}
@endif

Omschrijving van de opdracht
{{ $confirmation->descriptionText() }}

Met vriendelijke groet,
{{ $confirmation->sender_name ?: $senderCompany }}
{{ $senderCompany }}
