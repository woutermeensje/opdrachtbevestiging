<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offerte {{ $quote->reference }}</title>
</head>
@php
    $recipientName = $quote->client_contact_name ?: $quote->client_name;
    $recipientNameParts = preg_split('/\s+/', trim($recipientName));
    $recipientFirstName = $recipientNameParts[0] ?? $recipientName;
    $senderCompany = $quote->senderCompanyDisplayName();
    $themeBackground = '#FBFAF8';
    $blockStyle = 'width:100%;background:#ffffff;border:1px solid #dedede;border-radius:5px;box-sizing:border-box;padding:28px;';
    $textStyle = 'margin:0 0 12px;font-size:15px;line-height:1.6;color:#333333;';
    $dividerStyle = 'margin-top:24px;padding-top:20px;border-top:1px solid #dedede;';
@endphp
<body bgcolor="{{ $themeBackground }}" style="margin:0;padding:0;background:{{ $themeBackground }};font-family:Poppins,Arial,Helvetica,sans-serif;color:#333333;">
    <div style="width:100%;background:{{ $themeBackground }};padding:64px 24px;box-sizing:border-box;">
        <div style="max-width:720px;margin:0 auto;">
            <div style="{{ $blockStyle }}">
                <p style="{{ $textStyle }}">Hoi {{ $recipientFirstName }},</p>
                <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;">{{ $senderCompany }} heeft een offerte voor je opgesteld. De offerte vind je als bijlage bij deze e-mail.</p>

                <div style="{{ $dividerStyle }}">
                    <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#333333;">Met vriendelijke groet,</p>
                    <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;"><strong>{{ $quote->sender_name ?: $senderCompany }}</strong><br>{{ $senderCompany }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
