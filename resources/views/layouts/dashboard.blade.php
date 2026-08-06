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
                <p class="dashboard-topbar-greeting">Welkom terug, {{ auth()->user()->first_name }}</p>

                @if (auth()->user()->hasBillingAccess())
                    <details class="dashboard-topbar-create">
                        <summary class="dashboard-topbar-action">
                            <span>Aanmaken</span>
                            @include('partials.icons.icon', ['name' => 'chevron-down', 'size' => 16, 'class' => 'dashboard-topbar-action-icon'])
                        </summary>
                        <div class="dashboard-topbar-menu">
                            <a href="{{ route('dashboard.create') }}">Opdrachtbevestiging</a>
                            <a href="{{ route('dashboard.quotes.create') }}">Offerte</a>
                            <a href="{{ route('dashboard.contacts.create') }}">Opdrachtgever</a>
                        </div>
                    </details>
                @else
                    <a href="{{ route('billing.show') }}" class="dashboard-topbar-action">Abonnement kiezen</a>
                @endif
            </header>

            <main class="dashboard-main">
                @include('partials.dashboard.billing-banner')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
