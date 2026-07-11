<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="fPPs1bdTHYCUPew7OF7YKFYQOHCW9YAeqyUbZXEX8Tg">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>{{ $title ?? config('app.name') }}</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-body">
    <div class="dashboard-shell">
        @include('partials.dashboard.sidebar')

        <div class="dashboard-workspace">
            <header class="dashboard-topbar">
                <a href="{{ route('dashboard.create') }}" class="dashboard-topbar-action">Opdrachtbevestiging maken</a>
            </header>

            <main class="dashboard-main">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
