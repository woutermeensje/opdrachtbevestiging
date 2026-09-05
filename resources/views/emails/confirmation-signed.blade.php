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
    $themeBackground = '#FBFAF8';
    $blockStyle = 'width:100%;background:#ffffff;border:1px solid #dedede;border-radius:5px;box-sizing:border-box;padding:28px;';
    $labelStyle = 'margin:0 0 4px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;';
    $valueStyle = 'margin:0 0 16px;font-size:15px;line-height:1.5;color:#333333;';
    $textStyle = 'margin:0 0 12px;font-size:15px;line-height:1.6;color:#333333;';
    $dividerStyle = 'margin-top:24px;padding-top:20px;border-top:1px solid #dedede;';
@endphp
<body bgcolor="{{ $themeBackground }}" style="margin:0;padding:0;background:{{ $themeBackground }};font-family:Aptos,'Segoe UI',Arial,Helvetica,sans-serif;color:#333333;">
    <div style="width:100%;background:{{ $themeBackground }};padding:64px 24px;box-sizing:border-box;">
        <div style="max-width:720px;margin:0 auto;">
            <div style="{{ $blockStyle }}">
                <p style="margin:0 0 10px;font-size:13px;line-height:1.4;color:{{ $themePrimaryColor }};text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Opdrachtbevestiging getekend</p>
                <h1 style="margin:0 0 20px;font-size:28px;line-height:1.2;color:#333333;font-weight:700;">{{ $confirmation->title }}</h1>

                <p style="{{ $textStyle }}">Beste {{ $recipientName }},</p>

                @if ($forClient)
                    <p style="{{ $textStyle }}">Bedankt voor het akkoord geven op de opdrachtbevestiging met referentie {{ $confirmation->reference }}. Hierbij ontvang je de getekende opdrachtbevestiging als PDF-bijlage.</p>
                @else
                    <p style="{{ $textStyle }}">{{ $confirmation->client_contact_name ?: $confirmation->client_name }} heeft akkoord gegeven op de opdrachtbevestiging met referentie {{ $confirmation->reference }}. Hierbij ontvang je de getekende opdrachtbevestiging als PDF-bijlage.</p>
                @endif

                <div style="{{ $dividerStyle }}">
                    <p style="{{ $labelStyle }}">Getekend door</p>
                    <p style="{{ $valueStyle }}">{{ $confirmation->signer_name }}@if ($signedDate !== null) &middot; {{ $signedDate }}@endif</p>

                    <p style="{{ $labelStyle }}">Opdrachtnemer</p>
                    <p style="margin:0;font-size:15px;line-height:1.5;color:#333333;font-weight:700;">{{ $senderCompany }}</p>
                </div>

                @if ($forClient)
                    <div style="{{ $dividerStyle }}">
                        <p style="margin:0 0 6px;font-size:16px;line-height:1.5;color:#333333;font-weight:700;">Zelf ook opdrachtbevestigingen versturen?</p>
                        <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#333333;">Maak gratis een account aan op Opdrachtbevestiging.nl en stel binnen enkele minuten je eigen opdrachtbevestigingen op.</p>
                        <p style="margin:0;">
                            <a href="{{ route('register') }}" style="display:inline-block;padding:12px 18px;border-radius:5px;background:{{ $themePrimaryColor }};color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">Maak ook een account aan op Opdrachtbevestiging.nl</a>
                        </p>
                    </div>
                @endif

                <div style="{{ $dividerStyle }}">
                    <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#333333;">Met vriendelijke groet,</p>
                    <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;"><strong>{{ $confirmation->sender_name ?: $senderCompany }}</strong><br>{{ $senderCompany }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
