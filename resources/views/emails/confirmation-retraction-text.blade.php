@php
    $recipientName = $confirmation->client_contact_name ?: $confirmation->client_name;
    $senderCompany = $confirmation->user->company_name ?: $confirmation->sender_name;
@endphp
Opdrachtbevestiging {{ $confirmation->reference }} ingetrokken

Beste {{ $recipientName }},

De opdrachtbevestiging met referentie {{ $confirmation->reference }} is ingetrokken.

Je hoeft deze opdrachtbevestiging niet meer akkoord te bevestigen. Neem bij vragen rechtstreeks contact met ons op door op deze e-mail te reageren.

Met vriendelijke groet,
{{ $confirmation->sender_name ?: $senderCompany }}
{{ $senderCompany }}
