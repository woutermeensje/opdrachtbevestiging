@extends('layouts.page', [
    'title' => 'Wat is een opdrachtbevestiging?',
    'seoTitle' => 'Wat is een opdrachtbevestiging? | Uitleg en voorbeelden',
    'eyebrow' => 'Uitleg',
    'heading' => 'Wat is een opdrachtbevestiging?',
    'intro' => 'Een opdrachtbevestiging legt gemaakte afspraken schriftelijk vast, zodat beide partijen duidelijkheid hebben over inhoud, prijs, planning en voorwaarden.',
    'metaDescription' => 'Lees wat een opdrachtbevestiging is, wanneer je die gebruikt en waarom het belangrijk is om zakelijke afspraken schriftelijk vast te leggen.',
    'canonical' => route('pages.what-is-confirmation'),
])

@section('page-content')
    @include('partials.pages.content-card', [
        'title' => 'Wat is een opdrachtbevestiging?',
        'slot' => '
            <p>Een opdrachtbevestiging is een document waarin je afspraken over een opdracht schriftelijk bevestigt. Denk aan afspraken over werkzaamheden, prijs of tarief, planning, duur en aanvullende voorwaarden.</p>
            <p>Zo leg je vast wat mondeling, telefonisch of per e-mail is besproken, zodat daar later geen onduidelijkheid over ontstaat.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Wanneer gebruik je het?',
        'slot' => '
            <p>Een opdrachtbevestiging gebruik je zodra je werkzaamheden gaat uitvoeren op basis van afspraken die al zijn gemaakt, maar nog niet duidelijk schriftelijk zijn vastgelegd.</p>
            <p>Dat speelt bijvoorbeeld bij marketingdiensten, recruitment, fotografie, detachering, consultancy of andere zakelijke dienstverlening waarbij scope en voorwaarden goed moeten worden bevestigd.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Waarom is het belangrijk?',
        'slot' => '
            <p>Met een opdrachtbevestiging creëer je duidelijkheid vooraf. Beide partijen weten wat is afgesproken en welke verwachtingen daarbij horen.</p>
            <p>Dat helpt om misverstanden te voorkomen, geeft meer grip op het akkoordproces en zorgt voor een beter dossier als er later vragen ontstaan.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'class' => 'page-card-wide',
        'title' => 'Meer weten over het proces?',
        'slot' => '
            <p>Lees ook meer over <a href="'.e(route('pages.create-confirmation')).'">een opdrachtbevestiging opstellen</a> of bekijk <a href="'.e(route('pages.how-it-works')).'">hoe Opdrachtbevestiging.nl werkt</a>.</p>
        ',
    ])
@endsection
