<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="fPPs1bdTHYCUPew7OF7YKFYQOHCW9YAeqyUbZXEX8Tg">
    <meta name="description" content="{{ $metaDescription ?? 'Opdrachtbevestiging.nl helpt je om afspraken en opdrachtbevestigingen eenvoudig digitaal vast te leggen en te versturen.' }}">
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $ogTitle ?? ($title ?? config('app.name')) }}">
    <meta property="og:description" content="{{ $ogDescription ?? ($metaDescription ?? 'Opdrachtbevestiging.nl helpt je om afspraken en opdrachtbevestigingen eenvoudig digitaal vast te leggen en te versturen.') }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $ogTitle ?? ($title ?? config('app.name')) }}">
    <meta name="twitter:description" content="{{ $ogDescription ?? ($metaDescription ?? 'Opdrachtbevestiging.nl helpt je om afspraken en opdrachtbevestigingen eenvoudig digitaal vast te leggen en te versturen.') }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @isset($structuredData)
        <script type="application/ld+json">{!! $structuredData !!}</script>
    @endisset
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ $bodyClass ?? '' }}">
    @include('partials.navigation')

    <main class="{{ $mainClass ?? '' }}">
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
