<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Opdrachtbevestiging {{ $confirmation->reference }}</title>
    <style>
        @page {
            margin: 46px 52px;
        }

        body {
            margin: 0;
            color: #333333;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.55;
        }

        h1,
        h2,
        h3,
        p {
            margin-top: 0;
        }

        p {
            margin-bottom: 0;
        }

        .document {
            width: 100%;
        }

        .document-header {
            display: table;
            width: 100%;
            margin-bottom: 72px;
            table-layout: fixed;
        }

        .document-logo,
        .document-date {
            display: table-cell;
            vertical-align: top;
        }

        .document-logo {
            width: 64%;
        }

        .document-logo img {
            max-width: 180px;
            max-height: 86px;
        }

        .document-logo-text {
            display: inline-block;
            color: {{ $themePrimaryColor }};
            font-size: 44px;
            font-weight: bold;
            line-height: 1;
        }

        .document-date {
            width: 36%;
            text-align: right;
        }

        .document-label {
            margin: 0 0 8px;
            color: #333333;
            font-size: 16px;
            font-weight: bold;
            line-height: 1.2;
        }

        .document-date-value {
            color: #333333;
            font-size: 15px;
            font-weight: 300;
            line-height: 1.35;
        }

        .party-row {
            display: table;
            width: 100%;
            margin-bottom: 76px;
            table-layout: fixed;
        }

        .party-cell {
            display: table-cell;
            width: 50%;
            padding-right: 46px;
            vertical-align: top;
        }

        .party-cell-right {
            padding-right: 0;
            padding-left: 46px;
        }

        .party-cell p {
            color: #333333;
            font-size: 15px;
            font-weight: 300;
            line-height: 1.45;
        }

        .party-cell strong {
            font-weight: 300;
        }

        .document-content {
            margin-bottom: 34px;
        }

        .document-main-heading {
            margin: 0 0 48px;
            color: #333333;
            font-size: 16px;
            font-weight: bold;
            line-height: 1.25;
        }

        .document-field {
            margin-bottom: 34px;
        }

        .document-field-title {
            margin-bottom: 16px;
        }

        .document-title-value {
            margin: 0;
            color: #333333;
            font-size: 15px;
            font-weight: 300;
            line-height: 1.7;
        }

        .rich-content {
            max-width: 100%;
            color: #333333;
            font-size: 15px;
            font-weight: 300;
            line-height: 1.7;
        }

        .rich-content p {
            margin: 0 0 10px;
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
            font-size: 16px;
        }

        .rich-content h3 {
            font-size: 14px;
        }

        .rich-content ul,
        .rich-content ol {
            margin-top: 0;
            margin-bottom: 10px;
            padding-left: 18px;
        }

        .rich-content li {
            margin-bottom: 4px;
        }

        .rich-content blockquote {
            margin: 0 0 10px;
            padding: 6px 10px;
            border-left: 3px solid {{ $themePrimaryColor }};
            background: #f5f6f8;
            color: #4b5563;
        }

        .rich-content a {
            color: {{ $themePrimaryColor }};
            text-decoration: underline;
        }

        .supporting-section {
            margin: 0 0 22px;
            padding-top: 18px;
            border-top: 1px solid #dedede;
            page-break-inside: avoid;
        }

        .supporting-section .document-label {
            margin-bottom: 10px;
        }

        .details-table,
        .confirmation-specifications-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table th,
        .details-table td,
        .confirmation-specifications-table th,
        .confirmation-specifications-table td {
            padding: 6px 0;
            border-top: 1px solid #dedede;
            text-align: left;
            vertical-align: top;
        }

        .details-table tr:first-child th,
        .details-table tr:first-child td,
        .confirmation-specifications-table tr:first-child th,
        .confirmation-specifications-table tr:first-child td {
            border-top: 0;
        }

        .details-table th,
        .confirmation-specifications-table th {
            width: 34%;
            padding-right: 12px;
            color: #333333;
            font-weight: bold;
        }

        .confirmation-specifications-summary-section {
            margin-bottom: 16px;
        }

        .confirmation-specifications-summary-section:last-child {
            margin-bottom: 0;
        }

        .confirmation-specifications-summary-section h3 {
            margin: 0 0 8px;
            color: #333333;
            font-size: 14px;
            font-weight: bold;
        }

        .document-list {
            margin: 0;
            padding-left: 18px;
        }

        .document-list li {
            margin-bottom: 4px;
        }

        .meta {
            color: #666666;
            font-size: 12px;
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

        .footer-note {
            margin: 34px 0 0;
            padding-top: 14px;
            border-top: 1px solid #222222;
            color: #333333;
            font-size: 15px;
            font-weight: 300;
            line-height: 1.55;
            text-align: center;
        }

        .footer-note p {
            margin: 0;
        }
    </style>
</head>
@php
    $senderAddressLines = $confirmation->senderAddressLines();
    $clientAddressLines = $confirmation->clientAddressLines();
    $logo = $confirmation->senderCompanyLogoDataUri();
    $signature = $confirmation->signerSignatureDataUri();
    $documentDate = ($confirmation->created_at ?? $confirmation->pdf_generated_at ?? now())->format('d-m-Y');
    $documents = array_values(array_filter([
        $confirmation->terms_path ? 'Algemene voorwaarden: '.($confirmation->terms_original_name ?: basename($confirmation->terms_path)) : null,
        $confirmation->attachment_path ? 'Bijlage: '.($confirmation->attachment_original_name ?: basename($confirmation->attachment_path)) : null,
        $confirmation->quote_path ? 'Offerte: '.($confirmation->quote_original_name ?: basename($confirmation->quote_path)) : null,
    ]));
@endphp
<body>
    <main class="document">
        <header class="document-header">
            <div class="document-logo">
                @if ($logo !== null)
                    <img src="{{ $logo }}" alt="">
                @else
                    <span class="document-logo-text">{{ $confirmation->senderCompanyDisplayName() }}</span>
                @endif
            </div>
            <div class="document-date">
                <p class="document-label">Datum</p>
                <p class="document-date-value">{{ $documentDate }}</p>
            </div>
        </header>

        <div class="party-row">
            <section class="party-cell">
                <h2 class="document-label">Opdrachtnemer</h2>
                <p>
                    <strong>{{ $confirmation->senderCompanyDisplayName() }}</strong><br>
                    @foreach ($senderAddressLines as $line)
                        {{ $line }}<br>
                    @endforeach
                    {{ $confirmation->sender_email ?: '-' }}
                    @if (filled($confirmation->sender_kvk_number))
                        <br>KVK: {{ $confirmation->sender_kvk_number }}
                    @endif
                </p>
            </section>
            <section class="party-cell party-cell-right">
                <h2 class="document-label">Opdrachtgever</h2>
                <p>
                    <strong>{{ $confirmation->client_name }}</strong><br>
                    {{ $confirmation->client_contact_name ?: '-' }}<br>
                    {{ $confirmation->client_email }}
                    @foreach ($clientAddressLines as $line)
                        <br>{{ $line }}
                    @endforeach
                    @if (filled($confirmation->client_kvk_number))
                        <br>KVK: {{ $confirmation->client_kvk_number }}
                    @endif
                </p>
            </section>
        </div>

        <section class="document-content">
            <h1 class="document-main-heading">Opdrachtbevestiging opstellen</h1>

            <div class="document-field document-field-title">
                <h2 class="document-label">Titel</h2>
                <p class="document-title-value">{{ $confirmation->title }}</p>
            </div>

            <div class="document-field">
                <h2 class="document-label">Omschrijving</h2>
                <div class="rich-content">{!! $confirmation->descriptionHtml() !!}</div>
            </div>
        </section>

        <section class="supporting-section">
            <h2 class="document-label">Opdrachtgegevens</h2>
            <table class="details-table">
                <tbody>
                    <tr>
                        <th>Referentie</th>
                        <td>{{ $confirmation->reference }}</td>
                    </tr>
                    @if ($confirmation->agreement_date !== null)
                        <tr>
                            <th>Startdatum</th>
                            <td>{{ $confirmation->agreement_date->format('d-m-Y') }}</td>
                        </tr>
                    @endif
                    @if (filled($confirmation->duration))
                        <tr>
                            <th>Duur van de opdracht</th>
                            <td>{{ $confirmation->duration }}</td>
                        </tr>
                    @endif
                    @if ((float) $confirmation->total_value > 0)
                        <tr>
                            <th>Vergoeding</th>
                            <td>EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }} ({{ $confirmation->valueVatLabel() }})</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>

    @if ($confirmation->hasSpecifications())
        <section class="supporting-section">
            <h2 class="document-label">Aanvullende specificaties</h2>
            @include('partials.confirmations.specifications', ['confirmation' => $confirmation])
        </section>
    @endif

    @if ($confirmation->defaultAgreementsHtml() !== '')
        <section class="supporting-section">
            <h2 class="document-label">Basis afspraken</h2>
            <div class="rich-content">{!! $confirmation->defaultAgreementsHtml() !!}</div>
        </section>
    @endif

    @if ($documents !== [])
        <section class="supporting-section">
            <h2 class="document-label">Meegestuurde documenten</h2>
            <ul class="document-list">
                @foreach ($documents as $document)
                    <li>{{ $document }}</li>
                @endforeach
            </ul>
            @if ($confirmation->terms_path)
                <p class="meta">Op deze opdracht zijn de bijgevoegde algemene voorwaarden van toepassing.</p>
            @endif
        </section>
    @endif

    <section class="supporting-section">
        <h2 class="document-label">Akkoord en handtekening</h2>
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
    </section>

    @if ($confirmation->footerNoteText() !== '')
        <div class="footer-note">
            <p>{!! $confirmation->footerNoteHtml() !!}</p>
        </div>
    @endif
    </main>

</body>
</html>
