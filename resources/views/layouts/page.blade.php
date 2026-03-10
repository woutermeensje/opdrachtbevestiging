@extends('layouts.app', [
    'title' => $seoTitle ?? (($title ?? config('app.name')).' | Opdrachtbevestiging.nl'),
    'metaDescription' => $metaDescription ?? ($intro ?? 'Lees meer over opdrachtbevestigingen, het opstellen ervan en het digitaal vastleggen van zakelijke afspraken.'),
    'canonical' => $canonical ?? url()->current(),
    'ogTitle' => $ogTitle ?? ($seoTitle ?? (($title ?? config('app.name')).' | Opdrachtbevestiging.nl')),
    'ogDescription' => $ogDescription ?? ($metaDescription ?? ($intro ?? 'Lees meer over opdrachtbevestigingen en het digitaal vastleggen van afspraken.')),
    'structuredData' => $structuredData ?? json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $heading ?? ($title ?? config('app.name')),
        'description' => $metaDescription ?? ($intro ?? 'Lees meer over opdrachtbevestigingen en het digitaal vastleggen van afspraken.'),
        'url' => $canonical ?? url()->current(),
        'inLanguage' => 'nl-NL',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
])

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="page-eyebrow">{{ $eyebrow ?? config('app.name') }}</p>
            <h1>{{ $heading }}</h1>
            @isset($intro)
                <p class="page-intro">{{ $intro }}</p>
            @endisset
        </div>
    </section>

    <section class="page-content">
        <div class="container page-content-grid">
            @yield('page-content')
        </div>
    </section>
@endsection
