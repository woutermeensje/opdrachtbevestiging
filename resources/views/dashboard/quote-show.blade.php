@extends('layouts.dashboard', [
    'title' => $quote->title,
])

@php
    $emailAttachmentSummary = $quote->emailAttachmentSummary();
    $senderAddress = collect($quote->senderAddressLines())
        ->map(fn (string $line): string => e($line))
        ->implode('<br>');
    $pdfLine = $quote->hasPdf()
        ? '<p><strong>PDF:</strong> <a href="'.e(route('dashboard.quotes.pdf', $quote)).'">Download offerte</a></p>'
        : '<p><strong>PDF:</strong> Nog niet gegenereerd. De PDF wordt gemaakt bij verzending.</p>';
    $markActions = $quote->canBeMarked()
        ? '<form method="POST" action="'.e(route('dashboard.quotes.accept', $quote)).'" class="dashboard-action-form">
            '.csrf_field().'
            <button type="submit" class="btn btn-secondary">Markeer geaccepteerd</button>
        </form>
        <form method="POST" action="'.e(route('dashboard.quotes.reject', $quote)).'" class="dashboard-action-form">
            '.csrf_field().'
            <button type="submit" class="btn btn-danger">Markeer afgewezen</button>
        </form>'
        : '';
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Detail',
        'title' => $quote->title,
        'text' => 'Referentie '.$quote->reference.' voor '.$quote->client_name.' met contactpersoon '.$quote->client_contact_name.'.',
    ])

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Opdrachtgever',
            'slot' => '
                <p><strong>Bedrijf:</strong> '.e($quote->client_name).'</p>
                <p><strong>Contactpersoon:</strong> '.e($quote->client_contact_name ?: '-').'</p>
                <p><strong>E-mail:</strong> '.e($quote->client_email).'</p>
                <p><strong>KVK:</strong> '.e($quote->client_kvk_number ?: '-').'</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Opdrachtnemer',
            'slot' => '
                <p><strong>Bedrijf:</strong> '.e($quote->senderCompanyDisplayName()).'</p>
                <p><strong>KVK:</strong> '.e($quote->sender_kvk_number ?: '-').'</p>
                <p><strong>Adres:</strong><br>'.($senderAddress !== '' ? $senderAddress : '-').'</p>
                <p><strong>Contact:</strong> '.e($quote->sender_name ?: '-').' ('.e($quote->sender_email ?: '-').')</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Status en waarde',
            'slot' => '
                <p><strong>Status:</strong> '.e($quote->status).'</p>
                <p><strong>Offertebedrag:</strong> EUR '.e(number_format((float) $quote->total_value, 2, ',', '.')).' ('.e($quote->valueVatLabel()).')</p>
                <p><strong>Geldig tot:</strong> '.e(optional($quote->valid_until)->format('d-m-Y') ?? '-').'</p>
                '.$pdfLine.'
                <div class="dashboard-panel-actions">
                    <form method="POST" action="'.e(route('dashboard.quotes.send', $quote)).'" class="dashboard-action-form">
                        '.csrf_field().'
                        <button type="submit" class="btn btn-primary">Per e-mail versturen</button>
                    </form>
                    '.$markActions.'
                </div>
            ',
        ])
    </div>

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Omschrijving',
            'slot' => '<div class="dashboard-rich-content">'.$quote->descriptionHtml().'</div>',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'E-mailverzending',
            'slot' => '
                <p><strong>Afzender:</strong> '.e($quote->sender_name ?: '-').' ('.e($quote->sender_email ?: '-').')</p>
                <p><strong>Ontvanger:</strong> '.e($quote->client_contact_name ?: $quote->client_name).' ('.e($quote->client_email).')</p>
                <p><strong>Verzenddatum:</strong> '.e(optional($quote->sent_at)->format('d-m-Y') ?? '-').'</p>
                <p><strong>Bijlagen:</strong> '.e($emailAttachmentSummary ?: 'Geen bijlage toegevoegd.').'</p>
            ',
        ])
    </div>
@endsection
