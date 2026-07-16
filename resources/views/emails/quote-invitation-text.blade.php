@php
    $recipientName = $quote->client_contact_name ?: $quote->client_name;
    $recipientNameParts = preg_split('/\s+/', trim($recipientName));
    $recipientFirstName = $recipientNameParts[0] ?? $recipientName;
    $senderCompany = $quote->senderCompanyDisplayName();
@endphp
Hoi {{ $recipientFirstName }},

{{ $senderCompany }} heeft een offerte voor je opgesteld. De offerte vind je als bijlage bij deze e-mail.

Met vriendelijke groet,
{{ $quote->sender_name ?: $senderCompany }}
{{ $senderCompany }}
