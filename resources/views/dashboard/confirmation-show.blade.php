@extends('layouts.dashboard', [
    'title' => $confirmation->title,
])

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
            'title' => 'Status en waarde',
            'slot' => '
                <p><strong>Status:</strong> '.e($confirmation->status).'</p>
                <p><strong>Waarde:</strong> EUR '.e(number_format((float) $confirmation->total_value, 2, ',', '.')).'</p>
                <p><strong>Online voorbeeld:</strong> <a href="'.e($confirmation->publicUrl()).'" target="_blank" rel="noopener noreferrer">Open document</a></p>
                <form method="POST" action="'.e(route('dashboard.confirmations.send', $confirmation)).'" style="margin-top:12px;">
                    '.csrf_field().'
                    <button type="submit" class="btn btn-primary">Per e-mail versturen</button>
                </form>
            ',
        ])
    </div>

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Belangrijke data',
            'slot' => '
                <p><strong>Opdrachtdatum:</strong> '.e(optional($confirmation->agreement_date)->format('d-m-Y') ?? '-').'</p>
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
    </div>

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'E-mailverzending',
            'slot' => '
                <p><strong>Afzender:</strong> '.e($confirmation->sender_name ?: '-').' ('.e($confirmation->sender_email ?: '-').')</p>
                <p><strong>Ontvanger:</strong> '.e($confirmation->client_contact_name ?: $confirmation->client_name).' ('.e($confirmation->client_email).')</p>
                <p><strong>Inhoud:</strong> De volledige opdrachtbevestiging staat in de e-mailtekst. Er wordt geen bijlage meegestuurd.</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Akkoord',
            'slot' => '
                <p><strong>Akkoord door:</strong> '.e($confirmation->signer_name ?: '-').'</p>
                <p><strong>IP-adres:</strong> '.e($confirmation->signer_ip ?: '-').'</p>
                <p><strong>Laatste weergave:</strong> '.e(optional($confirmation->viewed_at)->format('d-m-Y H:i') ?? '-').'</p>
            ',
        ])
    </div>
@endsection
