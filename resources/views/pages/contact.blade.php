@extends('layouts.page', [
    'title' => 'Contact',
    'eyebrow' => 'Contact',
    'heading' => 'Contact',
    'intro' => 'Gebruik deze pagina voor vragen over de software, maatwerk of ondersteuning bij je proces rond opdrachtbevestigingen.',
])

@section('page-content')
    @include('partials.pages.content-card', [
        'title' => 'Plan een afspraak',
        'slot' => '
            <p>Hier komt straks de Calendly-link of embed te staan, zodat bezoekers direct een afspraak kunnen inplannen.</p>
            <p><strong>Plaatsaanduiding:</strong> vervang dit blok later met de definitieve Calendly-integratie.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Direct contact',
        'slot' => '
            <p>Stuur een e-mail naar <a href="mailto:info@opdrachtbevestiging.nl">info@opdrachtbevestiging.nl</a> voor vragen of een demo-aanvraag.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Waarmee we helpen',
        'slot' => '
            <p>We helpen bij het structureren van je opdrachtflow, het standaardiseren van templates en het verbeteren van de digitale akkoordroute.</p>
        ',
    ])

    @include('partials.pages.content-card', [
        'title' => 'Reactietijd',
        'slot' => '
            <p>Voor inhoudelijke vragen en demoverzoeken kun je uitgaan van een reactie binnen twee werkdagen.</p>
        ',
    ])
@endsection
