@extends('layouts.page', [
    'title' => 'Prijzen',
    'eyebrow' => 'Tarieven',
    'heading' => 'Prijzen',
    'intro' => 'Kies een opzet die past bij je volume en werkwijze. De exacte pakketten kunnen hier later verder worden uitgewerkt.',
])

@section('page-content')
    @include('partials.pages.content-card', [
        'title' => 'Start',
        'slot' => '
            <p>Voor zelfstandigen en kleine teams die professioneel willen starten met een beperkt aantal opdrachtbevestigingen per maand.</p>
            <p><strong>Indicatie:</strong> vanaf 19 euro per maand.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Groei',
        'slot' => '
            <p>Voor organisaties die meer volume draaien en extra grip willen op templates, statussen en opvolging.</p>
            <p><strong>Indicatie:</strong> vanaf 49 euro per maand.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Maatwerk',
        'slot' => '
            <p>Voor teams met specifieke workflows, meerdere gebruikers of aanvullende integraties en procesafspraken.</p>
            <p><strong>Indicatie:</strong> op aanvraag.</p>
        ',
    ])
@endsection
