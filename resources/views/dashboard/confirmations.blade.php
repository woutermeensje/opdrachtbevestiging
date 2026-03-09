@extends('layouts.dashboard', [
    'title' => 'Opdrachtbevestigingen',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Beheer',
        'title' => 'Opdrachtbevestigingen',
        'text' => 'Hier verzamel je alle verzonden, concept- en afgeronde opdrachtbevestigingen op één plek.',
    ])

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    @if ($confirmations->isEmpty())
        @include('partials.dashboard.panel', [
            'title' => 'Nog geen opdrachtbevestigingen',
            'slot' => '
                <p>Er zijn nog geen opdrachtbevestigingen toegevoegd. Zodra je de aanmaakflow invult, verschijnen ze hier in je overzicht.</p>
                <p><a href="'.e(route('dashboard.create')).'" class="btn btn-primary">Eerste opdrachtbevestiging aanmaken</a></p>
            ',
        ])
    @else
        @include('partials.dashboard.panel', [
            'title' => 'Overzicht',
            'slot' => view('partials.dashboard.confirmations-table', ['confirmations' => $confirmations])->render(),
        ])
    @endif
@endsection
