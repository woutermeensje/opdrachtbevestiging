<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Opdrachtbevestiging {{ $confirmation->reference }} ingetrokken</title>
</head>
@php
    $recipientName = $confirmation->client_contact_name ?: $confirmation->client_name;
    $senderCompany = $confirmation->user->company_name ?: $confirmation->sender_name;
@endphp
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#333333;">
    <div style="width:100%;background:#ffffff;padding:32px 16px;box-sizing:border-box;">
        <div style="max-width:720px;margin:0 auto;">
            <p style="margin:0 0 10px;font-size:13px;line-height:1.4;color:#7C5CFA;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Opdrachtbevestiging</p>
            <h1 style="margin:0 0 12px;font-size:28px;line-height:1.2;color:#333333;font-weight:700;">{{ $confirmation->title }}</h1>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#333333;">Beste {{ $recipientName }},</p>
            <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#333333;">De opdrachtbevestiging met referentie {{ $confirmation->reference }} is ingetrokken.</p>
            <p style="margin:0 0 28px;font-size:15px;line-height:1.6;color:#333333;">Je hoeft deze opdrachtbevestiging niet meer akkoord te bevestigen. Neem bij vragen rechtstreeks contact met ons op door op deze e-mail te reageren.</p>
            <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#333333;">Met vriendelijke groet,</p>
            <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;"><strong>{{ $confirmation->sender_name ?: $senderCompany }}</strong><br>{{ $senderCompany }}</p>
        </div>
    </div>
</body>
</html>
