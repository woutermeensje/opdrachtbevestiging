<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Opdrachtbevestiging {{ $confirmation->reference }}</title>
</head>
@php
    $recipientName = $confirmation->client_contact_name ?: $confirmation->client_name;
    $senderCompany = $confirmation->user->company_name ?: $confirmation->sender_name;
@endphp
<body style="margin:0;padding:32px;background:#f5f5f2;font-family:Arial,sans-serif;color:#242424;">
    <div style="max-width:720px;margin:0 auto;background:#ffffff;border:1px solid #deded7;border-radius:6px;padding:32px;">
        <p style="margin:0 0 12px;font-size:13px;color:#4f6f52;text-transform:uppercase;letter-spacing:.06em;">Opdrachtbevestiging</p>
        <h1 style="margin:0 0 10px;font-size:28px;line-height:1.2;color:#1f2a24;">{{ $confirmation->title }}</h1>
        <p style="margin:0 0 24px;color:#62665f;">Referentie {{ $confirmation->reference }}</p>

        <p style="margin:0 0 12px;">Beste {{ $recipientName }},</p>
        <p style="margin:0 0 12px;">Hierbij bevestigen wij de opdracht zoals hieronder uitgewerkt. Deze e-mail bevat de volledige opdrachtbevestiging; er is geen bijlage toegevoegd.</p>
        <p style="margin:0 0 28px;">Je kunt rechtstreeks op deze e-mail reageren bij vragen of om akkoord te geven.</p>

        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:0 0 24px;">
            <tr>
                <td style="width:50%;vertical-align:top;padding:16px;border:1px solid #deded7;background:#fafaf7;">
                    <p style="margin:0 0 8px;font-size:13px;color:#62665f;text-transform:uppercase;letter-spacing:.04em;">Opdrachtnemer</p>
                    <p style="margin:0;"><strong>{{ $senderCompany }}</strong><br>
                    {{ $confirmation->sender_name ?: '-' }}<br>
                    {{ $confirmation->sender_email ?: '-' }}</p>
                </td>
                <td style="width:50%;vertical-align:top;padding:16px;border:1px solid #deded7;background:#fafaf7;">
                    <p style="margin:0 0 8px;font-size:13px;color:#62665f;text-transform:uppercase;letter-spacing:.04em;">Opdrachtgever</p>
                    <p style="margin:0;"><strong>{{ $confirmation->client_name }}</strong><br>
                    {{ $confirmation->client_contact_name ?: '-' }}<br>
                    {{ $confirmation->client_email }}</p>
                </td>
            </tr>
        </table>

        <div style="padding:18px 20px;background:#f0f5f0;border-left:4px solid #4f6f52;margin:0 0 24px;">
            <p style="margin:0 0 8px;"><strong>Opdrachtdatum:</strong> {{ optional($confirmation->agreement_date)->format('d-m-Y') ?? 'Niet ingevuld' }}</p>
            <p style="margin:0 0 8px;"><strong>Waarde:</strong> EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }}</p>
            <p style="margin:0;"><strong>Vervaldatum:</strong> {{ optional($confirmation->expires_at)->format('d-m-Y') ?? 'Niet ingesteld' }}</p>
        </div>

        <h2 style="margin:0 0 12px;font-size:18px;color:#1f2a24;">Omschrijving van de opdracht</h2>
        <div style="margin:0 0 28px;white-space:pre-line;line-height:1.6;">{{ $confirmation->description ?: 'Geen omschrijving toegevoegd.' }}</div>

        <hr style="border:0;border-top:1px solid #deded7;margin:0 0 20px;">

        <p style="margin:0 0 8px;">Met vriendelijke groet,</p>
        <p style="margin:0;"><strong>{{ $confirmation->sender_name ?: $senderCompany }}</strong><br>{{ $senderCompany }}</p>
    </div>
</body>
</html>
