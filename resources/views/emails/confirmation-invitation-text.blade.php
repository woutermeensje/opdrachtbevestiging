@php
    $recipientName = $confirmation->client_contact_name ?: $confirmation->client_name;
    $senderCompany = $confirmation->senderCompanyDisplayName();
    $acceptUrl = $confirmation->publicUrl();
@endphp
Opdrachtbevestiging {{ $confirmation->reference }}: {{ $confirmation->title }}

Beste {{ $recipientName }},

{{ $senderCompany }} heeft een opdrachtbevestiging opgesteld! Je kan via deze link de opdrachtbevestiging bekijken en accorderen:
{{ $acceptUrl }}

Met vriendelijke groet,
{{ $confirmation->sender_name ?: $senderCompany }}
{{ $senderCompany }}
