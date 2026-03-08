@extends('layouts.dashboard', [
    'title' => 'Dashboard',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Overzicht',
        'title' => 'Dashboard',
        'text' => 'Welkom terug. Kies links een onderdeel om nieuwe opdrachtbevestigingen te maken of bestaande dossiers te beheren.',
    ])

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Snelle start',
            'slot' => '
                <p>Start met een nieuwe opdrachtbevestiging, open je bestaande documenten of werk je profiel bij.</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Account',
            'slot' => '
                <p>Je bent ingelogd als <strong>'.e(auth()->user()->first_name.' '.auth()->user()->last_name).'</strong> bij <strong>'.e(auth()->user()->company_name).'</strong>.</p>
            ',
        ])
    </div>
@endsection
