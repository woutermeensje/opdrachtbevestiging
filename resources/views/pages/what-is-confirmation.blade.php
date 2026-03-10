@extends('layouts.page', [
    'title' => 'Wat is een opdrachtbevestiging?',
    'eyebrow' => 'Uitleg',
    'heading' => 'Wat is een opdrachtbevestiging?',
    'intro' => 'Deze pagina is aangemaakt als basis voor uitleg over wat een opdrachtbevestiging is, wanneer je die gebruikt en waarom het document belangrijk is.',
])

@section('page-content')
    @include('partials.pages.content-card', [
        'title' => 'Basisuitleg',
        'slot' => '
            <p>Gebruik dit blok voor een heldere introductie op het begrip opdrachtbevestiging en de rol ervan binnen een zakelijke samenwerking.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Wanneer gebruik je het?',
        'slot' => '
            <p>Hier kun je straks toelichten in welke situaties een opdrachtbevestiging nodig of verstandig is, bijvoorbeeld voorafgaand aan de uitvoering van werkzaamheden.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Waarom is het belangrijk?',
        'slot' => '
            <p>Dit blok kan later worden ingevuld met de voordelen voor duidelijkheid, verwachtingen, akkoord en dossiervorming.</p>
        ',
    ])
@endsection
