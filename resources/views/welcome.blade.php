@extends('layouts.app', [
    'title' => 'Opdrachtbevestiging.nl | Jouw afspraken eenvoudig vastleggen',
    'metaDescription' => 'Leg mondelinge, telefonische en per e-mail gemaakte afspraken eenvoudig schriftelijk vast met Opdrachtbevestiging.nl. Maak, verstuur en laat digitaal ondertekenen.',
    'canonical' => url('/'),
    'ogTitle' => 'Opdrachtbevestiging.nl | Jouw afspraken eenvoudig vastleggen',
    'ogDescription' => 'Software om mondelinge afspraken en opdrachtbevestigingen eenvoudig schriftelijk vast te leggen en digitaal te versturen.',
    'structuredData' => json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                'name' => 'Opdrachtbevestiging.nl',
                'url' => url('/'),
                'description' => 'Softwareoplossing om mondelinge afspraken en opdrachtbevestigingen eenvoudig schriftelijk vast te leggen en digitaal te versturen.',
                'inLanguage' => 'nl-NL',
            ],
            [
                '@type' => 'Organization',
                'name' => 'Opdrachtbevestiging.nl',
                'url' => url('/'),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
])

@section('content')
    @include('partials.home.hero')
    @include('partials.home.content-block')
@endsection
