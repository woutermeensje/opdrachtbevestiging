@extends('layouts.dashboard', [
    'title' => $confirmation->title,
])

@php
    $emailAttachmentSummary = $confirmation->emailAttachmentSummary();
    $senderAddress = collect($confirmation->senderAddressLines())
        ->map(fn (string $line): string => e($line))
        ->implode('<br>');
    $pdfLine = $confirmation->hasPdf()
        ? '<p><strong>PDF:</strong> <a href="'.e(route('dashboard.confirmations.pdf', $confirmation)).'">Download opdrachtbevestiging</a></p>'
        : '<p><strong>PDF:</strong> Nog niet gegenereerd. De PDF wordt gemaakt bij verzending.</p>';
    $termsLine = $confirmation->terms_path
        ? '<p><strong>Algemene voorwaarden:</strong> '.e($confirmation->terms_original_name ?: basename($confirmation->terms_path)).'</p>'
        : '<p><strong>Algemene voorwaarden:</strong> Niet toegevoegd.</p>';
    $signatureLine = $confirmation->signer_signature_path
        ? '<p><strong>Handtekening:</strong> Vastgelegd in de PDF.</p>'
        : '<p><strong>Handtekening:</strong> Nog niet gezet.</p>';
    $retractForm = $confirmation->canBeRetracted()
        ? '<form method="POST" action="'.e(route('dashboard.confirmations.retract', $confirmation)).'" class="dashboard-action-form" onsubmit="return confirm(\'Weet je zeker dat je deze opdrachtbevestiging wilt intrekken?\');">
            '.csrf_field().'
            <button type="submit" class="btn btn-danger">Intrekken</button>
        </form>'
        : '';
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Detail',
        'title' => $confirmation->title,
        'text' => 'Referentie '.$confirmation->reference.' voor '.$confirmation->client_name.' met contactpersoon '.$confirmation->client_contact_name.'.',
    ])

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Opdrachtgever',
            'slot' => '
                <p><strong>Bedrijf:</strong> '.e($confirmation->client_name).'</p>
                <p><strong>Contactpersoon:</strong> '.e($confirmation->client_contact_name ?: '-').'</p>
                <p><strong>E-mail:</strong> '.e($confirmation->client_email).'</p>
                <p><strong>KVK:</strong> '.e($confirmation->client_kvk_number ?: '-').'</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Opdrachtnemer',
            'slot' => '
                <p><strong>Bedrijf:</strong> '.e($confirmation->senderCompanyDisplayName()).'</p>
                <p><strong>KVK:</strong> '.e($confirmation->sender_kvk_number ?: '-').'</p>
                <p><strong>Adres:</strong><br>'.($senderAddress !== '' ? $senderAddress : '-').'</p>
                <p><strong>Contact:</strong> '.e($confirmation->sender_name ?: '-').' ('.e($confirmation->sender_email ?: '-').')</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Status en waarde',
            'slot' => '
                <p><strong>Status:</strong> '.e($confirmation->status).'</p>
                <p><strong>Vergoeding:</strong> EUR '.e(number_format((float) $confirmation->total_value, 2, ',', '.')).' ('.e($confirmation->valueVatLabel()).')</p>
                <p><strong>Online voorbeeld:</strong> <a href="'.e($confirmation->publicUrl()).'" target="_blank" rel="noopener noreferrer">Open document</a></p>
                '.$pdfLine.'
                <div class="dashboard-panel-actions">
                    <form method="POST" action="'.e(route('dashboard.confirmations.send', $confirmation)).'" class="dashboard-action-form">
                        '.csrf_field().'
                        <button type="submit" class="btn btn-primary">Per e-mail versturen</button>
                    </form>
                    '.$retractForm.'
                </div>
            ',
        ])
    </div>

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Belangrijke data',
            'slot' => '
                <p><strong>Startdatum:</strong> '.e(optional($confirmation->agreement_date)->format('d-m-Y') ?? '-').'</p>
                <p><strong>Duur van de opdracht:</strong> '.e($confirmation->duration ?: '-').'</p>
                <p><strong>Verzenddatum:</strong> '.e(optional($confirmation->sent_at)->format('d-m-Y') ?? '-').'</p>
                <p><strong>Akkoorddatum:</strong> '.e(optional($confirmation->signed_at)->format('d-m-Y') ?? '-').'</p>
                <p><strong>Vervaldatum:</strong> '.e(optional($confirmation->expires_at)->format('d-m-Y') ?? '-').'</p>
                <p><strong>Bekeken op:</strong> '.e(optional($confirmation->viewed_at)->format('d-m-Y H:i') ?? '-').'</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Omschrijving',
            'slot' => '<div class="dashboard-rich-content">'.$confirmation->descriptionHtml().'</div>',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Vaste afspraken',
            'slot' => '
                '.($confirmation->defaultAgreementsHtml() !== '' ? '<div class="dashboard-rich-content">'.$confirmation->defaultAgreementsHtml().'</div>' : '<p>Geen basis afspraken toegevoegd.</p>').'
                <p><strong>Beëindiging van de opdracht:</strong> '.e($confirmation->termination_terms ?: '-').'</p>
                '.$termsLine.'
            ',
        ])
    </div>

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'E-mailverzending',
            'slot' => '
                <p><strong>Afzender:</strong> '.e($confirmation->sender_name ?: '-').' ('.e($confirmation->sender_email ?: '-').')</p>
                <p><strong>Ontvanger:</strong> '.e($confirmation->client_contact_name ?: $confirmation->client_name).' ('.e($confirmation->client_email).')</p>
                <p><strong>Inhoud:</strong> De volledige opdrachtbevestiging staat in de e-mailtekst.</p>
                <p><strong>Bijlagen:</strong> '.e($emailAttachmentSummary ?: 'Geen bijlage of offerte toegevoegd.').'</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Akkoord',
            'slot' => '
                <p><strong>Akkoord door:</strong> '.e($confirmation->signer_name ?: '-').'</p>
                <p><strong>IP-adres:</strong> '.e($confirmation->signer_ip ?: '-').'</p>
                '.$signatureLine.'
                <p><strong>Laatste weergave:</strong> '.e(optional($confirmation->viewed_at)->format('d-m-Y H:i') ?? '-').'</p>
            ',
        ])
    </div>
@endsection
