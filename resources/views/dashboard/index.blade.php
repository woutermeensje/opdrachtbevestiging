@extends('layouts.dashboard', [
    'title' => 'Dashboard',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Overzicht',
        'title' => 'Dashboard',
        'text' => 'Welkom terug. Kies links een onderdeel om nieuwe opdrachtbevestigingen te maken of bestaande dossiers te beheren.',
    ])

    @unless (auth()->user()->hasCompletedCompanyProfile())
        <div class="dashboard-notice dashboard-notice-warning">
            Vul eerst je bedrijfsgegevens aan in <a href="{{ route('dashboard.profile') }}">Mijn profiel</a>. Pas daarna kun je opdrachtbevestigingen aanmaken en versturen.
        </div>
    @endunless

    @if ($recentConfirmations->isNotEmpty())
        @include('partials.dashboard.panel', [
            'title' => 'Recente opdrachtbevestigingen',
            'class' => 'dashboard-panel-wide',
            'slot' => view('partials.dashboard.confirmations-table', ['confirmations' => $recentConfirmations])->render(),
        ])
    @endif

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Snelle start',
            'slot' => '
                <p>Voeg eerst een opdrachtgever toe via Contacten. Daarna kun je een nieuwe opdrachtbevestiging aanmaken en als volledige e-mailtekst versturen.</p>
                <p><a href="'.e(route('dashboard.contacts')).'" class="btn btn-primary">Contact toevoegen</a></p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Account',
            'slot' => '
                <p>Je bent ingelogd als <strong>'.e(auth()->user()->first_name.' '.auth()->user()->last_name).'</strong>'.(auth()->user()->company_name ? ' bij <strong>'.e(auth()->user()->company_name).'</strong>' : '').'.</p>
            ',
        ])
    </div>
@endsection
