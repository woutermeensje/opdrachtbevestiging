<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Opdrachtbevestiging {{ $confirmation->reference }}</title>
</head>
@php
    $recipientName = $confirmation->client_contact_name ?: $confirmation->client_name;
    $senderCompany = $confirmation->senderCompanyDisplayName();
    $senderAddressLines = $confirmation->senderAddressLines();
    $totalValue = (float) $confirmation->total_value;
    $emailAttachmentSummary = $confirmation->emailAttachmentSummary();
    $acceptUrl = $confirmation->publicUrl();
@endphp
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#333333;">
    <div style="width:100%;background:#ffffff;padding:32px 16px;box-sizing:border-box;">
        <div style="max-width:720px;margin:0 auto;">
            <p style="margin:0 0 10px;font-size:13px;line-height:1.4;color:#003B73;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Opdrachtbevestiging</p>
            <h1 style="margin:0 0 12px;font-size:28px;line-height:1.2;color:#333333;font-weight:700;">{{ $confirmation->title }}</h1>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#333333;">Beste {{ $recipientName }},</p>
            <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#333333;">Hierbij bevestigen wij de opdracht zoals hieronder uitgewerkt. Deze e-mail bevat de volledige opdrachtbevestiging @if ($emailAttachmentSummary !== ''). Bijgevoegd: {{ $emailAttachmentSummary }}@endif.</p>
            <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#333333;">Klik op de knop hieronder om de opdrachtbevestiging digitaal te bekijken en akkoord te geven.</p>
            <p style="margin:0 0 28px;">
                <a href="{{ $acceptUrl }}" style="display:inline-block;padding:12px 18px;border-radius:5px;background:#003B73;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">Akkoord geven!</a>
            </p>

            <div style="width:100%;background:#FBFAF8;border:1px solid #dedede;border-radius:5px;overflow:hidden;box-sizing:border-box;margin:0 0 28px;">
                <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                    <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Referentie</p>
                    <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;">{{ $confirmation->reference }}</p>
                </div>

                <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                    <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Titel</p>
                    <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;font-weight:700;">{{ $confirmation->title }}</p>
                </div>

                @if ($confirmation->agreement_date !== null)
                    <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                        <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Startdatum</p>
                        <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;">{{ $confirmation->agreement_date->format('d-m-Y') }}</p>
                    </div>
                @endif

                @if (filled($confirmation->duration))
                    <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                        <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Duur van de opdracht</p>
                        <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;">{{ $confirmation->duration }}</p>
                    </div>
                @endif

                @if ($totalValue > 0)
                    <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                        <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Vergoeding</p>
                        <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;">EUR {{ number_format($totalValue, 2, ',', '.') }} ({{ $confirmation->valueVatLabel() }})</p>
                    </div>
                @endif

                @if ($confirmation->expires_at !== null)
                    <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                        <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Vervaldatum</p>
                        <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;">{{ $confirmation->expires_at->format('d-m-Y') }}</p>
                    </div>
                @endif

                <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                    <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Opdrachtgever</p>
                    <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;font-weight:700;">{{ $confirmation->client_name }}</p>
                    <p style="margin:4px 0 0;font-size:15px;line-height:1.5;color:#333333;">{{ $confirmation->client_contact_name ?: '-' }}</p>
                    <p style="margin:4px 0 0;font-size:15px;line-height:1.5;color:#333333;">{{ $confirmation->client_email }}</p>
                    @if (filled($confirmation->client_kvk_number))
                        <p style="margin:4px 0 0;font-size:15px;line-height:1.5;color:#333333;">KVK: {{ $confirmation->client_kvk_number }}</p>
                    @endif
                </div>

                <div style="padding:20px 22px;border-bottom:1px solid #dedede;">
                    <p style="margin:0 0 6px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Opdrachtnemer</p>
                    <p style="margin:0;font-size:16px;line-height:1.5;color:#333333;font-weight:700;">{{ $senderCompany }}</p>
                    @foreach ($senderAddressLines as $line)
                        <p style="margin:4px 0 0;font-size:15px;line-height:1.5;color:#333333;">{{ $line }}</p>
                    @endforeach
                    @if (filled($confirmation->sender_kvk_number))
                        <p style="margin:4px 0 0;font-size:15px;line-height:1.5;color:#333333;">KVK: {{ $confirmation->sender_kvk_number }}</p>
                    @endif
                    <p style="margin:4px 0 0;font-size:15px;line-height:1.5;color:#333333;">{{ $confirmation->sender_name ?: '-' }}</p>
                    <p style="margin:4px 0 0;font-size:15px;line-height:1.5;color:#333333;">{{ $confirmation->sender_email ?: '-' }}</p>
                </div>

                <div style="padding:20px 22px;">
                    <p style="margin:0 0 10px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Omschrijving van de opdracht</p>
                    <div style="margin:0;font-size:15px;line-height:1.7;color:#333333;">{!! $confirmation->descriptionHtml() !!}</div>
                </div>

                @if ($confirmation->defaultAgreementsHtml() !== '')
                    <div style="padding:20px 22px;border-top:1px solid #dedede;">
                        <p style="margin:0 0 10px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Basis afspraken</p>
                        <div style="margin:0;font-size:15px;line-height:1.7;color:#333333;">{!! $confirmation->defaultAgreementsHtml() !!}</div>
                    </div>
                @endif

                @if ($confirmation->terminationTermsHtml() !== '')
                    <div style="padding:20px 22px;border-top:1px solid #dedede;">
                        <p style="margin:0 0 10px;font-size:12px;line-height:1.4;color:#333333;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Beëindiging van de opdracht</p>
                        <div style="margin:0;font-size:15px;line-height:1.7;color:#333333;">{!! $confirmation->terminationTermsHtml() !!}</div>
                    </div>
                @endif

                @if ($confirmation->terms_path)
                    <div style="padding:20px 22px;border-top:1px solid #dedede;">
                        <p style="margin:0;font-size:14px;line-height:1.6;color:#666666;">Op deze opdracht zijn de bijgevoegde algemene voorwaarden van toepassing.</p>
                    </div>
                @endif
            </div>

            <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#333333;">Met vriendelijke groet,</p>
            <p style="margin:0;font-size:15px;line-height:1.6;color:#333333;"><strong>{{ $confirmation->sender_name ?: $senderCompany }}</strong><br>{{ $senderCompany }}</p>
        </div>
    </div>
</body>
</html>
