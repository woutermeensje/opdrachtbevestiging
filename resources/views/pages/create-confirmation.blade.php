@extends('layouts.page', [
    'title' => 'Opdrachtbevestiging opstellen',
    'seoTitle' => 'Opdrachtbevestiging opstellen | Praktische uitleg',
    'eyebrow' => 'Praktisch',
    'heading' => 'Opdrachtbevestiging opstellen',
    'intro' => 'Een goede opdrachtbevestiging bevat duidelijke afspraken over werkzaamheden, tarieven, planning, looptijd en voorwaarden.',
    'metaDescription' => 'Lees hoe je een opdrachtbevestiging opstelt en welke onderdelen je moet opnemen, zoals werkzaamheden, tarieven, planning en voorwaarden.',
    'canonical' => route('pages.create-confirmation'),
])

@section('page-content')
    @include('partials.pages.content-card', [
        'title' => 'Welke onderdelen neem je op?',
        'slot' => '
            <p>Neem in elk geval op welke werkzaamheden worden uitgevoerd, tegen welk tarief of welke totaalprijs, binnen welke termijn en onder welke voorwaarden.</p>
            <p>Vaak horen daar ook een startdatum, einddatum, betaalafspraken en eventuele uitzonderingen of aanvullende afspraken bij.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Waar let je op?',
        'slot' => '
            <p>Zorg dat de tekst concreet en begrijpelijk is. Hoe duidelijker je formuleert wat wel en niet binnen de opdracht valt, hoe kleiner de kans op discussie achteraf.</p>
            <p>Controleer ook of tarieven, termijnen en voorwaarden aansluiten op wat daadwerkelijk is besproken met de opdrachtgever.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Van opstellen naar akkoord',
        'slot' => '
            <p>Nadat je de opdrachtbevestiging hebt opgesteld, stuur je deze naar de opdrachtgever ter controle en ondertekening. Daarmee maak je het akkoordproces overzichtelijk en traceerbaar.</p>
            <p>Met Opdrachtbevestiging.nl kun je dit proces centraal beheren, zodat je documenten, statussen en gegevens op één plek houdt.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'class' => 'page-card-wide',
        'title' => 'Gerelateerde pagina\'s',
        'slot' => '
            <p>Wil je eerst meer achtergrond? Lees dan <a href="'.e(route('pages.what-is-confirmation')).'">wat een opdrachtbevestiging is</a> of bekijk direct <a href="'.e(route('pages.pricing')).'">de prijzen</a>.</p>
        ',
    ])
@endsection
