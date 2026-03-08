@extends('layouts.dashboard', [
    'title' => 'Opdrachtbevestigingen',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Beheer',
        'title' => 'Opdrachtbevestigingen',
        'text' => 'Hier verzamel je alle verzonden, concept- en afgeronde opdrachtbevestigingen op één plek.',
    ])

    @include('partials.dashboard.panel', [
        'title' => 'Overzicht',
        'slot' => '
            <p>Er zijn nog geen opdrachtbevestigingen toegevoegd. Zodra je de aanmaakflow invult, kun je ze hier beheren en filteren.</p>
        ',
    ])
@endsection
