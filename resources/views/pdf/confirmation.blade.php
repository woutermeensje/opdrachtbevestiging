<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Opdrachtbevestiging {{ $confirmation->reference }}</title>
    <style>
        @page {
            margin: 34px 38px;
        }

        body {
            margin: 0;
            color: #333333;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        h1,
        h2,
        h3,
        p {
            margin-top: 0;
        }

        h1 {
            margin-bottom: 8px;
            font-size: 24px;
            line-height: 1.2;
        }

        h2 {
            margin-bottom: 8px;
            font-size: 15px;
        }

        h3 {
            margin-bottom: 6px;
            font-size: 13px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 26px;
            border-bottom: 1px solid #dedede;
            padding-bottom: 18px;
        }

        .header-main,
        .header-logo {
            display: table-cell;
            vertical-align: top;
        }

        .header-logo {
            width: 150px;
            text-align: right;
        }

        .header-logo img {
            max-width: 130px;
            max-height: 80px;
        }

        .eyebrow {
            margin-bottom: 5px;
            color: #003B73;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .meta {
            color: #666666;
        }

        .grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 18px;
        }

        .box {
            margin-bottom: 18px;
            border: 1px solid #dedede;
            padding: 14px 16px;
        }

        .rich-content p {
            margin-bottom: 8px;
        }

        .rich-content ul,
        .rich-content ol {
            margin-top: 0;
            padding-left: 18px;
        }

        .document-list {
            margin: 0;
            padding-left: 18px;
        }

        .confirmation-specifications-summary-section {
            margin-bottom: 12px;
        }

        .confirmation-specifications-summary-section:last-child {
            margin-bottom: 0;
        }

        .confirmation-specifications-table {
            width: 100%;
            border-collapse: collapse;
        }

        .confirmation-specifications-table th,
        .confirmation-specifications-table td {
            padding: 5px 0;
            border-top: 1px solid #dedede;
            text-align: left;
            vertical-align: top;
        }

        .confirmation-specifications-table th {
            width: 34%;
            padding-right: 12px;
            color: #666666;
        }

        .signature-box {
            min-height: 110px;
            margin-top: 10px;
            border: 1px dashed #999999;
            padding: 12px;
        }

        .signature-image {
            max-width: 260px;
            max-height: 92px;
            margin-top: 8px;
        }

        .signature-line {
            margin-top: 55px;
            border-top: 1px solid #999999;
            padding-top: 6px;
            color: #666666;
        }
    </style>
</head>
@php
    $senderAddressLines = $confirmation->senderAddressLines();
    $logo = $confirmation->senderCompanyLogoDataUri();
    $signature = $confirmation->signerSignatureDataUri();
    $documents = array_values(array_filter([
        $confirmation->terms_path ? 'Algemene voorwaarden: '.($confirmation->terms_original_name ?: basename($confirmation->terms_path)) : null,
        $confirmation->attachment_path ? 'Bijlage: '.($confirmation->attachment_original_name ?: basename($confirmation->attachment_path)) : null,
        $confirmation->quote_path ? 'Offerte: '.($confirmation->quote_original_name ?: basename($confirmation->quote_path)) : null,
    ]));
@endphp
<body>
    <div class="header">
        <div class="header-main">
            <p class="eyebrow">Opdrachtbevestiging</p>
            <h1>{{ $confirmation->title }}</h1>
            <p class="meta">
                Referentie {{ $confirmation->reference }}
                @if ($confirmation->agreement_date !== null)
                    <br>Startdatum {{ $confirmation->agreement_date->format('d-m-Y') }}
                @endif
                @if (filled($confirmation->duration))
                    <br>Duur van de opdracht: {{ $confirmation->duration }}
                @endif
                @if ((float) $confirmation->total_value > 0)
                    <br>Vergoeding: EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }} ({{ $confirmation->valueVatLabel() }})
                @endif
            </p>
        </div>
        @if ($logo !== null)
            <div class="header-logo">
                <img src="{{ $logo }}" alt="">
            </div>
        @endif
    </div>

    <div class="grid">
        <div class="cell">
            <h2>Opdrachtgever</h2>
            <p>
                <strong>{{ $confirmation->client_name }}</strong><br>
                {{ $confirmation->client_contact_name ?: '-' }}<br>
                {{ $confirmation->client_email }}
                @if (filled($confirmation->client_kvk_number))
                    <br>KVK: {{ $confirmation->client_kvk_number }}
                @endif
            </p>
        </div>
        <div class="cell">
            <h2>Opdrachtnemer</h2>
            <p>
                <strong>{{ $confirmation->senderCompanyDisplayName() }}</strong><br>
                @foreach ($senderAddressLines as $line)
                    {{ $line }}<br>
                @endforeach
                @if (filled($confirmation->sender_kvk_number))
                    KVK: {{ $confirmation->sender_kvk_number }}<br>
                @endif
                {{ $confirmation->sender_name ?: '-' }}<br>
                {{ $confirmation->sender_email ?: '-' }}
            </p>
        </div>
    </div>

    <div class="box">
        <h2>Omschrijving van de opdracht</h2>
        <div class="rich-content">{!! $confirmation->descriptionHtml() !!}</div>
    </div>

    @if ($confirmation->hasSpecifications())
        <div class="box">
            <h2>Aanvullende specificaties</h2>
            @include('partials.confirmations.specifications', ['confirmation' => $confirmation])
        </div>
    @endif

    @if ($confirmation->defaultAgreementsHtml() !== '')
        <div class="box">
            <h2>Basis afspraken</h2>
            <div class="rich-content">{!! $confirmation->defaultAgreementsHtml() !!}</div>
        </div>
    @endif

    @if ($documents !== [])
        <div class="box">
            <h2>Meegestuurde documenten</h2>
            <ul class="document-list">
                @foreach ($documents as $document)
                    <li>{{ $document }}</li>
                @endforeach
            </ul>
            @if ($confirmation->terms_path)
                <p class="meta">Op deze opdracht zijn de bijgevoegde algemene voorwaarden van toepassing.</p>
            @endif
        </div>
    @endif

    <div class="box">
        <h2>Akkoord en handtekening</h2>
        @if ($confirmation->signed_at !== null)
            <p>
                Akkoord gegeven door <strong>{{ $confirmation->signer_name ?: '-' }}</strong><br>
                Datum: {{ $confirmation->signed_at->format('d-m-Y H:i') }}<br>
                @if (filled($confirmation->signer_ip))
                    IP-adres: {{ $confirmation->signer_ip }}
                @endif
            </p>
            @if ($signature !== null)
                <img class="signature-image" src="{{ $signature }}" alt="">
            @endif
        @else
            <div class="signature-box">
                <p class="meta">Ruimte voor digitale handtekening na akkoord.</p>
                <p class="signature-line">Naam en handtekening opdrachtgever</p>
            </div>
        @endif
    </div>

    <p class="meta">
        Deze opdrachtbevestiging is opgesteld door {{ $confirmation->senderCompanyDisplayName() }}.
    </p>
</body>
</html>
