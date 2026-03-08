@extends('layouts.page', [
    'title' => 'Hoe het werkt',
    'eyebrow' => 'Werkwijze',
    'heading' => 'Hoe het werkt',
    'intro' => 'Van eerste klantvraag tot akkoord: dit is de route waarmee je opdrachtbevestigingen sneller en consistenter afhandelt.',
])

@section('page-content')
    @include('partials.pages.content-card', [
        'title' => '1. Gegevens verzamelen',
        'slot' => '
            <p>Maak een dossier aan en verzamel alle relevante klant-, project- en bedrijfsgegevens op één plek.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => '2. Opdrachtbevestiging opstellen',
        'slot' => '
            <p>Gebruik een vaste template en vul diensten, tarieven, planning en voorwaarden in zonder handmatig knip- en plakwerk.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => '3. Verzenden en opvolgen',
        'slot' => '
            <p>Stuur de opdrachtbevestiging digitaal uit, volg de status en houd alle versies centraal beschikbaar.</p>
        ',
    ])
@endsection
