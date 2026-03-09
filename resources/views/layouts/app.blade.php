<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="fPPs1bdTHYCUPew7OF7YKFYQOHCW9YAeqyUbZXEX8Tg">
    <title>{{ $title ?? config('app.name') }}</title>
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
