@extends('layouts.dashboard', [
    'title' => 'Mijn profiel',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Profiel',
        'title' => 'Mijn profiel',
        'text' => 'Beheer hier je persoonlijke gegevens en bedrijfsinformatie.',
    ])

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Gebruiker',
            'slot' => '
                <p><strong>Naam:</strong> '.e(auth()->user()->first_name.' '.auth()->user()->last_name).'</p>
                <p><strong>E-mail:</strong> '.e(auth()->user()->email).'</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Bedrijf',
            'slot' => '
                <p><strong>Bedrijfsnaam:</strong> '.e(auth()->user()->company_name).'</p>
                <p>Hier kun je later je profielgegevens bewerken.</p>
            ',
        ])
    </div>
@endsection
