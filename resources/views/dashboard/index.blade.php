@extends('layouts.dashboard', [
    'title' => 'Dashboard',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Overzicht',
        'title' => 'Dashboard',
        'text' => 'Welkom terug. Kies links een onderdeel om nieuwe opdrachtbevestigingen te maken of bestaande dossiers te beheren.',
    ])

    <div class="dashboard-metrics">
        <article class="dashboard-metric-card">
            <p class="dashboard-metric-label">Totaal</p>
            <strong>{{ $metrics['total'] }}</strong>
        </article>
        <article class="dashboard-metric-card">
            <p class="dashboard-metric-label">Concepten</p>
            <strong>{{ $metrics['drafts'] }}</strong>
        </article>
        <article class="dashboard-metric-card">
            <p class="dashboard-metric-label">Getekend</p>
            <strong>{{ $metrics['signed'] }}</strong>
        </article>
        <article class="dashboard-metric-card">
            <p class="dashboard-metric-label">Totale waarde</p>
            <strong>EUR {{ number_format($metrics['value'], 2, ',', '.') }}</strong>
        </article>
    </div>

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Snelle start',
            'slot' => '
                <p>Start met een nieuwe opdrachtbevestiging, bekijk lopende akkoordverzoeken en houd per dossier waarde en status bij.</p>
                <p><a href="'.e(route('dashboard.create')).'" class="btn btn-primary">Nieuwe opdrachtbevestiging</a></p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Account',
            'slot' => '
                <p>Je bent ingelogd als <strong>'.e(auth()->user()->first_name.' '.auth()->user()->last_name).'</strong> bij <strong>'.e(auth()->user()->company_name).'</strong>.</p>
            ',
        ])
    </div>

    @if ($recentConfirmations->isNotEmpty())
        @include('partials.dashboard.panel', [
            'title' => 'Recente opdrachtbevestigingen',
            'class' => 'dashboard-panel-wide',
            'slot' => view('partials.dashboard.confirmations-table', ['confirmations' => $recentConfirmations])->render(),
        ])
    @endif
@endsection
