@extends('layouts.page', [
    'title' => 'Opdrachtbevestiging opstellen',
    'eyebrow' => 'Praktisch',
    'heading' => 'Opdrachtbevestiging opstellen',
    'intro' => 'Deze pagina is aangemaakt als basis voor content over het opstellen van een opdrachtbevestiging, inclusief opbouw, aandachtspunten en vervolgstappen.',
])

@section('page-content')
    @include('partials.pages.content-card', [
        'title' => 'Welke onderdelen neem je op?',
        'slot' => '
            <p>Gebruik dit blok om straks de vaste onderdelen van een opdrachtbevestiging te beschrijven, zoals scope, tarieven, planning en voorwaarden.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Waar let je op?',
        'slot' => '
            <p>Hier kan later tekst komen over formulering, volledigheid, juridische duidelijkheid en het voorkomen van misverstanden.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Van opstellen naar akkoord',
        'slot' => '
            <p>Dit blok is bedoeld voor uitleg over verzenden, controleren, laten accorderen en het vastleggen van de definitieve versie.</p>
        ',
    ])
@endsection
