<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="fPPs1bdTHYCUPew7OF7YKFYQOHCW9YAeqyUbZXEX8Tg">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <meta property="og:locale" content="nl_NL">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $ogTitle ?? $title }}">
    <meta property="og:description" content="{{ $ogDescription ?? $metaDescription }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:site_name" content="Opdrachtbevestiging.nl">
    <meta property="og:image" content="{{ asset('images/logo-icon.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $ogTitle ?? $title }}">
    <meta name="twitter:description" content="{{ $ogDescription ?? $metaDescription }}">
    <title>{{ $title }}</title>
    @include('partials.favicon')
    @isset($structuredData)
        <script type="application/ld+json">{!! $structuredData !!}</script>
    @endisset
    @vite(['resources/css/marketing.css'])
</head>
<body class="mk-body {{ $bodyClass ?? '' }}">
    <a class="mk-skip-link" href="#content">Ga naar de inhoud</a>

    @include('partials.marketing.header')

    <main id="content">
        @yield('content')
    </main>

    @include('partials.marketing.footer')
</body>
</html>
