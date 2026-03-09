<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Opdrachtbevestiging {{ $confirmation->reference }}</title>
</head>
<body style="margin:0;padding:32px;background:#f6f2eb;font-family:Arial,sans-serif;color:#2f2f2f;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #eadfce;border-radius:8px;padding:32px;">
        <p style="margin:0 0 12px;font-size:14px;color:#9a6a27;text-transform:uppercase;letter-spacing:.08em;">Opdrachtbevestiging</p>
        <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;">{{ $confirmation->title }}</h1>

        <p style="margin:0 0 12px;">Beste {{ $confirmation->client_name }},</p>
        <p style="margin:0 0 12px;">Er staat een opdrachtbevestiging voor je klaar met referentie <strong>{{ $confirmation->reference }}</strong>.</p>
        <p style="margin:0 0 24px;">Klik op de knop hieronder om het document te bekijken en digitaal te bevestigen of te ondertekenen in je browser.</p>

        <p style="margin:0 0 24px;">
            <a href="{{ $confirmation->publicUrl() }}" style="display:inline-block;background:#3a89ff;color:#ffffff;text-decoration:none;padding:14px 18px;border-radius:5px;font-weight:700;">
                Opdrachtbevestiging openen
            </a>
        </p>

        <div style="padding:16px;background:#faf6f0;border-radius:5px;">
            <p style="margin:0 0 8px;"><strong>Klant:</strong> {{ $confirmation->client_name }}</p>
            <p style="margin:0 0 8px;"><strong>Waarde:</strong> EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }}</p>
            <p style="margin:0;"><strong>Vervaldatum:</strong> {{ optional($confirmation->expires_at)->format('d-m-Y') ?? 'Niet ingesteld' }}</p>
        </div>
    </div>
</body>
</html>
