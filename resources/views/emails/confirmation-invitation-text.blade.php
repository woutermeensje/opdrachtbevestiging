@php
    $recipientName = $confirmation->client_contact_name ?: $confirmation->client_name;
    $recipientNameParts = preg_split('/\s+/', trim($recipientName));
    $recipientFirstName = $recipientNameParts[0] ?? $recipientName;
    $senderCompany = $confirmation->senderCompanyDisplayName();
    $acceptUrl = $confirmation->publicUrl();
@endphp
Hoi {{ $recipientFirstName }},

{{ $senderCompany }} heeft een opdrachtbevestiging opgesteld! Je kan via deze link de opdrachtbevestiging bekijken en accorderen:
{{ $acceptUrl }}

Met vriendelijke groet,
{{ $confirmation->sender_name ?: $senderCompany }}
{{ $senderCompany }}
