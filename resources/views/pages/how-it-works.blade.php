@extends('layouts.page', [
    'title' => 'Hoe het werkt',
    'seoTitle' => 'Hoe Werkt Opdrachtbevestiging.nl? | Opdrachtbevestiging maken en versturen',
    'eyebrow' => 'Werkwijze',
    'heading' => 'Hoe het werkt',
    'intro' => 'Opdrachtbevestiging.nl helpt je om afspraken gestructureerd vast te leggen en digitaal ter ondertekening te versturen.',
    'metaDescription' => 'Ontdek hoe Opdrachtbevestiging.nl werkt: account aanmaken, opdrachtbevestiging opstellen, opdrachtgever toevoegen en digitaal versturen ter ondertekening.',
    'canonical' => route('pages.how-it-works'),
])

@section('page-content')
    @include('partials.pages.content-card', [
        'title' => '1. Maak een account aan',
        'slot' => '
            <p>Je start met het aanmaken van een account binnen Opdrachtbevestiging.nl. Vanuit daar werk je in een centrale omgeving waarin je opdrachtbevestigingen kunt opstellen en beheren.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => '2. Opdrachtbevestiging opstellen',
        'slot' => '
            <p>Klik op het aanmaken van een opdrachtbevestiging en vul de relevante gegevens in, zoals prijs of tarief, duur, start- en einddatum en de belangrijkste voorwaarden.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => '3. Opdrachtgever toevoegen en versturen',
        'slot' => '
            <p>Vervolgens voeg je jouw opdrachtgever toe via de Kamer van Koophandel API en verstuur je de opdrachtbevestiging ter ondertekening naar de andere partij.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'class' => 'page-card-wide',
        'title' => 'Waarom deze werkwijze handig is',
        'slot' => '
            <p>Met deze aanpak leg je afspraken die mondeling, telefonisch of per e-mail zijn gemaakt alsnog duidelijk schriftelijk vast.</p>
            <p>Dat helpt om vooraf helderheid te creëren over prijs, planning, voorwaarden en verwachtingen, zonder dat je daar een ingewikkeld juridisch proces van hoeft te maken.</p>
        ',
    ])
@endsection
