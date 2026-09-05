<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Opdrachtbevestiging {{ $confirmation->reference }}</title>
</head>
@php
    $recipientName = $confirmation->client_contact_name ?: $confirmation->client_name;
    $recipientNameParts = preg_split('/\s+/', trim($recipientName));
    $recipientFirstName = $recipientNameParts[0] ?? $recipientName;
    $senderCompany = $confirmation->senderCompanyDisplayName();
    $acceptUrl = $confirmation->publicUrl();
    $themeBackground = '#FBFAF8';
    $blockStyle = 'width:100%;background:#ffffff;border:1px solid #dedede;border-radius:5px;box-sizing:border-box;padding:28px;';
    $textStyle = 'margin:0 0 12px;font-size:15px;line-height:1.6;color:#333333;';
    $dividerStyle = 'margin-top:24px;padding-top:20px;border-top:1px solid #dedede;';
@endphp
<body bgcolor="{{ $themeBackground }}" style="margin:0;padding:0;background:{{ $themeBackground }};font-family:Aptos,'Segoe UI',Arial,Helvetica,sans-serif;color:#333333;">
    <div style="width:100%;background:{{ $themeBackground }};padding:64px 24px;box-sizing:border-box;">
        <div style="max-width:720px;margin:0 auto;">
            <div style="{{ $blockStyle }}">
                <p style="{{ $textStyle }}">Hoi {{ $recipientFirstName }},</p>
                <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;">{{ $senderCompany }} heeft een opdrachtbevestiging opgesteld! Je kan via <a href="{{ $acceptUrl }}" style="color:{{ $themePrimaryColor }};font-weight:700;">deze link</a> de opdrachtbevestiging bekijken en accorderen.</p>

                <p style="margin:20px 0 0;">
                    <a href="{{ $acceptUrl }}" style="display:inline-block;padding:12px 18px;border-radius:5px;background:{{ $themePrimaryColor }};color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">Opdrachtbevestiging bekijken</a>
                </p>

                <div style="{{ $dividerStyle }}">
                    <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#333333;">Met vriendelijke groet,</p>
                    <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;"><strong>{{ $confirmation->sender_name ?: $senderCompany }}</strong><br>{{ $senderCompany }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
