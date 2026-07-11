@php
    $recipientName = $forClient
        ? ($confirmation->client_contact_name ?: $confirmation->client_name)
        : ($confirmation->sender_name ?: $confirmation->senderCompanyDisplayName());
    $senderCompany = $confirmation->senderCompanyDisplayName();
    $signedDate = $confirmation->signed_at?->format('d-m-Y H:i');
@endphp
Opdrachtbevestiging {{ $confirmation->reference }} is getekend

Beste {{ $recipientName }},

@if ($forClient)
Bedankt voor het akkoord geven op de opdrachtbevestiging met referentie {{ $confirmation->reference }}. Hierbij ontvang je de getekende opdrachtbevestiging als PDF-bijlage.
@else
{{ $confirmation->client_contact_name ?: $confirmation->client_name }} heeft akkoord gegeven op de opdrachtbevestiging met referentie {{ $confirmation->reference }}. Hierbij ontvang je de getekende opdrachtbevestiging als PDF-bijlage.
@endif

Referentie: {{ $confirmation->reference }}
Getekend door: {{ $confirmation->signer_name }}
@if ($signedDate !== null)
Datum: {{ $signedDate }}
@endif
Opdrachtnemer: {{ $senderCompany }}

@if ($forClient)
Zelf ook opdrachtbevestigingen versturen? Maak gratis een account aan op Opdrachtbevestiging.nl:
{{ route('register') }}
@endif

Met vriendelijke groet,
{{ $confirmation->sender_name ?: $senderCompany }}
{{ $senderCompany }}
