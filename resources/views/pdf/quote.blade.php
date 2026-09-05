<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Offerte {{ $quote->reference }}</title>
    @include('pdf.partials.fonts')
    <style>
        @page {
            margin: 34px 38px;
        }

        body {
            margin: 0;
            color: #333333;
            font-family: 'Aptos', 'DejaVu Sans', Arial, sans-serif;
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
            color: {{ $themePrimaryColor }};
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

        .rich-content h1,
        .rich-content h2,
        .rich-content h3 {
            margin: 0 0 8px;
            color: #333333;
            font-weight: bold;
            line-height: 1.3;
        }

        .rich-content h1 {
            font-size: 18px;
        }

        .rich-content h2 {
            font-size: 15px;
        }

        .rich-content h3 {
            font-size: 13px;
        }

        .rich-content ul,
        .rich-content ol {
            margin-top: 0;
            margin-bottom: 8px;
            padding-left: 18px;
        }

        .rich-content li {
            margin-bottom: 4px;
        }

        .rich-content blockquote {
            margin: 0 0 8px;
            padding: 6px 10px;
            border-left: 3px solid {{ $themePrimaryColor }};
            background: #f5f6f8;
            color: #4b5563;
        }

        .rich-content a {
            color: {{ $themePrimaryColor }};
            text-decoration: underline;
        }

        .document-list {
            margin: 0;
            padding-left: 18px;
        }
    </style>
</head>
@php
    $senderAddressLines = $quote->senderAddressLines();
    $logo = $quote->senderCompanyLogoDataUri();
    $documents = array_values(array_filter([
        $quote->attachment_path ? 'Bijlage: '.($quote->attachment_original_name ?: basename($quote->attachment_path)) : null,
    ]));
@endphp
<body>
    <div class="header">
        <div class="header-main">
            <p class="eyebrow">Offerte</p>
            <h1>{{ $quote->title }}</h1>
            <p class="meta">
                Referentie {{ $quote->reference }}
                @if ($quote->valid_until !== null)
                    <br>Geldig tot {{ $quote->valid_until->format('d-m-Y') }}
                @endif
                @if ((float) $quote->total_value > 0)
                    <br>Offertebedrag: EUR {{ number_format((float) $quote->total_value, 2, ',', '.') }} ({{ $quote->valueVatLabel() }})
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
                <strong>{{ $quote->client_name }}</strong><br>
                {{ $quote->client_contact_name ?: '-' }}<br>
                {{ $quote->client_email }}
                @if (filled($quote->client_kvk_number))
                    <br>KVK: {{ $quote->client_kvk_number }}
                @endif
            </p>
        </div>
        <div class="cell">
            <h2>Opdrachtnemer</h2>
            <p>
                <strong>{{ $quote->senderCompanyDisplayName() }}</strong><br>
                @foreach ($senderAddressLines as $line)
                    {{ $line }}<br>
                @endforeach
                @if (filled($quote->sender_kvk_number))
                    KVK: {{ $quote->sender_kvk_number }}<br>
                @endif
                {{ $quote->sender_name ?: '-' }}<br>
                {{ $quote->sender_email ?: '-' }}
            </p>
        </div>
    </div>

    <div class="box">
        <h2>Omschrijving</h2>
        <div class="rich-content">{!! $quote->descriptionHtml() !!}</div>
    </div>

    @if ($documents !== [])
        <div class="box">
            <h2>Meegestuurde documenten</h2>
            <ul class="document-list">
                @foreach ($documents as $document)
                    <li>{{ $document }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="meta">
        Deze offerte is opgesteld door {{ $quote->senderCompanyDisplayName() }}.
    </p>
</body>
</html>
