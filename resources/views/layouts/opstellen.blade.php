{{--
    Gedeelde opmaak voor resources/views/opstellen/*, gelijk aan de oude
    WordPress-pagina's: tekstkaart met h1, screenshot, overzicht van alle typen.
    Verwacht van de view: $metaDescription en een @section('body').
    $type en $typeName komen uit OpstellenController.
--}}
@php
    $heading = $typeName.' opstellen';
    $title = $heading.' - Opdrachtbevestiging.nl';
    $canonical = route('opstellen.show', $type);
@endphp

@extends('layouts.marketing', [
    'title' => $title,
    'metaDescription' => $metaDescription,
    'canonical' => $canonical,
    'structuredData' => json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebPage',
                '@id' => $canonical,
                'url' => $canonical,
                'name' => $title,
                'description' => $metaDescription,
                'inLanguage' => 'nl-NL',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $heading, 'item' => $canonical],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
])

@section('content')
    <section class="mk-section">
        <div class="mk-card mk-card--padded mk-prose">
            <h1 class="mk-section__title">{{ $heading }}</h1>
            @yield('body')
        </div>
    </section>

    <section class="mk-section">
        <div class="mk-card mk-card--media">
            <img src="{{ asset('images/opstellen/opdrachtbevestiging-opstellen.png') }}" alt="Opdrachtbevestiging opstellen" width="1536" height="771" loading="lazy" decoding="async">
        </div>
    </section>

    <section class="mk-section" aria-labelledby="mk-types-title">
        <h2 id="mk-types-title" class="mk-section__title">Alle typen bevestigingen:</h2>
        @include('partials.marketing.opstellen-directory')
    </section>
@endsection
