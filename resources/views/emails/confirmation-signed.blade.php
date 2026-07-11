<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Opdrachtbevestiging {{ $confirmation->reference }} is getekend</title>
</head>
@php
    $recipientName = $forClient
        ? ($confirmation->client_contact_name ?: $confirmation->client_name)
        : ($confirmation->sender_name ?: $confirmation->senderCompanyDisplayName());
    $senderCompany = $confirmation->senderCompanyDisplayName();
    $signedDate = $confirmation->signed_at?->format('d-m-Y H:i');
@endphp
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#333333;">
    <div style="width:100%;background:#ffffff;padding:32px 16px;box-sizing:border-box;">
        <div style="max-width:720px;margin:0 auto;">
            <p style="margin:0 0 10px;font-size:13px;line-height:1.4;color:#003B73;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Opdrachtbevestiging getekend</p>
            <h1 style="margin:0 0 12px;font-size:28px;line-height:1.2;color:#333333;font-weight:700;">{{ $confirmation->title }}</h1>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#333333;">Beste {{ $recipientName }},</p>

            @if ($forClient)
                <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#333333;">Bedankt voor het akkoord geven op de opdrachtbevestiging met referentie {{ $confirmation->reference }}. Hierbij ontvang je de getekende opdrachtbevestiging als PDF-bijlage.</p>
            @else
                <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#333333;">{{ $confirmation->client_contact_name ?: $confirmation->client_name }} heeft akkoord gegeven op de opdrachtbevestiging met referentie {{ $confirmation->reference }}. Hierbij ontvang je de getekende opdrachtbevestiging als PDF-bijlage.</p>
            @endif

            <div style="width:100%;background:#FBFAF8;border:1px solid #dedede;border-radius:5px;overflow:hidden;box-sizing:border-box;margin:0 0 28px;">
                <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                    <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Referentie</p>
                    <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;">{{ $confirmation->reference }}</p>
                </div>

                <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                    <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Getekend door</p>
                    <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;">{{ $confirmation->signer_name }}</p>
                    @if ($signedDate !== null)
                        <p style="margin:4px 0 0;font-size:15px;line-height:1.5;color:#333333;">{{ $signedDate }}</p>
                    @endif
                </div>

                <div style="padding:20px 22px;">
                    <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Opdrachtnemer</p>
                    <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;font-weight:700;">{{ $senderCompany }}</p>
                </div>
            </div>

            @if ($forClient)
                <div style="width:100%;background:#FBFAF8;border:1px solid #dedede;border-radius:5px;overflow:hidden;box-sizing:border-box;padding:22px;margin:0 0 28px;">
                    <p style="margin:0 0 6px;font-size:16px;line-height:1.5;color:#333333;font-weight:700;">Zelf ook opdrachtbevestigingen versturen?</p>
                    <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#333333;">Maak gratis een account aan op Opdrachtbevestiging.nl en stel binnen enkele minuten je eigen opdrachtbevestigingen op.</p>
                    <p style="margin:0;">
                        <a href="{{ route('register') }}" style="display:inline-block;padding:12px 18px;border-radius:5px;background:#0C76DA;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">Maak ook een account aan op Opdrachtbevestiging.nl</a>
                    </p>
                </div>
            @endif

            <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#333333;">Met vriendelijke groet,</p>
            <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;"><strong>{{ $confirmation->sender_name ?: $senderCompany }}</strong><br>{{ $senderCompany }}</p>
        </div>
    </div>
</body>
</html>
