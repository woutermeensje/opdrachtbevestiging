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
Opdrachtdatum: {{ optional($confirmation->agreement_date)->format('d-m-Y') ?? 'Niet ingevuld' }}
Waarde: EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }}
Vervaldatum: {{ optional($confirmation->expires_at)->format('d-m-Y') ?? 'Niet ingesteld' }}

Omschrijving van de opdracht
{{ $confirmation->description ?: 'Geen omschrijving toegevoegd.' }}

Met vriendelijke groet,
{{ $confirmation->sender_name ?: $senderCompany }}
{{ $senderCompany }}
