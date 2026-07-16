@extends('layouts.dashboard', [
    'title' => 'Offertes',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Beheer',
        'title' => 'Mijn offertes',
        'text' => 'Hier verzamel je alle verzonden, concept- en afgeronde offertes op één plek.',
    ])

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    @if ($quotes->isEmpty())
        @include('partials.dashboard.panel', [
            'title' => 'Nog geen offertes',
            'slot' => '
                <p>Er zijn nog geen offertes toegevoegd. Zodra je de aanmaakflow invult, verschijnen ze hier in je overzicht.</p>
                <p><a href="'.e(route('dashboard.quotes.create')).'" class="btn btn-primary">Eerste offerte aanmaken</a></p>
            ',
        ])
    @else
        @include('partials.dashboard.panel', [
            'title' => 'Overzicht',
            'slot' => view('partials.dashboard.quotes-table', ['quotes' => $quotes])->render(),
        ])
    @endif
@endsection
