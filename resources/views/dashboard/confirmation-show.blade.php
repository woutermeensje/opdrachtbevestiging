@extends('layouts.dashboard', [
    'title' => $confirmation->title,
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Detail',
        'title' => $confirmation->title,
        'text' => 'Referentie '.$confirmation->reference.' voor '.$confirmation->client_name.'.',
    ])

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Klant',
            'slot' => '
                <p><strong>Naam:</strong> '.e($confirmation->client_name).'</p>
                <p><strong>E-mail:</strong> '.e($confirmation->client_email).'</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Status en waarde',
            'slot' => '
                <p><strong>Status:</strong> '.e($confirmation->status).'</p>
                <p><strong>Waarde:</strong> EUR '.e(number_format((float) $confirmation->total_value, 2, ',', '.')).'</p>
                <p><strong>Publieke link:</strong> <a href="'.e($confirmation->publicUrl()).'" target="_blank" rel="noopener noreferrer">Open document</a></p>
                <form method="POST" action="'.e(route('dashboard.confirmations.send', $confirmation)).'" style="margin-top:12px;">
                    '.csrf_field().'
                    <button type="submit" class="btn btn-primary">Versturen naar klant</button>
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
                <p><strong>Tekendatum:</strong> '.e(optional($confirmation->signed_at)->format('d-m-Y') ?? '-').'</p>
                <p><strong>Vervaldatum:</strong> '.e(optional($confirmation->expires_at)->format('d-m-Y') ?? '-').'</p>
                <p><strong>Bekeken op:</strong> '.e(optional($confirmation->viewed_at)->format('d-m-Y H:i') ?? '-').'</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Omschrijving',
            'slot' => '<p>'.e($confirmation->description ?: 'Geen omschrijving toegevoegd.').'</p>',
        ])
    </div>
@endsection
