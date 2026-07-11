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
    $themeBackground = '#FBFAF8';
    $blockStyle = 'width:100%;background:#ffffff;border:1px solid #dedede;border-radius:5px;box-sizing:border-box;padding:28px;';
    $textStyle = 'margin:0 0 12px;font-size:15px;line-height:1.6;color:#333333;';
    $dividerStyle = 'margin-top:24px;padding-top:20px;border-top:1px solid #dedede;';
@endphp
<body bgcolor="{{ $themeBackground }}" style="margin:0;padding:0;background:{{ $themeBackground }};font-family:Poppins,Arial,Helvetica,sans-serif;color:#333333;">
    <div style="width:100%;background:{{ $themeBackground }};padding:32px 16px;box-sizing:border-box;">
        <div style="max-width:720px;margin:0 auto;">
            <div style="{{ $blockStyle }}">
                <p style="margin:0 0 10px;font-size:13px;line-height:1.4;color:#003B73;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Opdrachtbevestiging</p>
                <h1 style="margin:0 0 20px;font-size:28px;line-height:1.2;color:#333333;font-weight:700;">{{ $confirmation->title }}</h1>

                <p style="{{ $textStyle }}">Beste {{ $recipientName }},</p>
                <p style="{{ $textStyle }}">De opdrachtbevestiging met referentie {{ $confirmation->reference }} is ingetrokken.</p>
                <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;">Je hoeft deze opdrachtbevestiging niet meer akkoord te bevestigen. Neem bij vragen rechtstreeks contact met ons op door op deze e-mail te reageren.</p>

                <div style="{{ $dividerStyle }}">
                    <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#333333;">Met vriendelijke groet,</p>
                    <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;"><strong>{{ $confirmation->sender_name ?: $senderCompany }}</strong><br>{{ $senderCompany }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
