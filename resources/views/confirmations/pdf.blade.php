<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Opdrachtbevestiging {{ $confirmation->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; line-height: 1.5; }
        .page { padding: 24px; }
        .eyebrow { color: #8b5e1a; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        h1 { margin: 8px 0 16px; font-size: 24px; }
        h2 { margin: 24px 0 8px; font-size: 14px; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .grid td { width: 50%; vertical-align: top; padding: 8px 12px 8px 0; }
        .box { border: 1px solid #ddd2c0; background: #faf7f2; padding: 12px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="eyebrow">Opdrachtbevestiging</div>
        <h1>{{ $confirmation->title }}</h1>

        <p>Referentie: <strong>{{ $confirmation->reference }}</strong></p>

        <table class="grid">
            <tr>
                <td>
                    <h2>Verzender</h2>
                    <p><strong>{{ $confirmation->user->company_name }}</strong><br>
                    {{ $confirmation->sender_name }}<br>
                    {{ $confirmation->sender_email }}</p>
                </td>
                <td>
                    <h2>Opdrachtgever</h2>
                    <p><strong>{{ $confirmation->client_name }}</strong><br>
                    {{ $confirmation->client_contact_name }}<br>
                    {{ $confirmation->client_email }}</p>
                </td>
            </tr>
        </table>

        <div class="box">
            <p><strong>Opdrachtdatum:</strong> {{ optional($confirmation->agreement_date)->format('d-m-Y') ?? 'Niet ingevuld' }}</p>
            <p><strong>Waarde:</strong> EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }}</p>
            <p><strong>Vervaldatum:</strong> {{ optional($confirmation->expires_at)->format('d-m-Y') ?? 'Niet ingesteld' }}</p>
        </div>

        <h2>Omschrijving</h2>
        <p>{!! nl2br(e($confirmation->description ?: 'Geen omschrijving toegevoegd.')) !!}</p>

        <h2>Ondertekening</h2>
        <p>Dit document is bedoeld voor digitale ondertekening via Signhost door beide partijen.</p>
    </div>
</body>
</html>
