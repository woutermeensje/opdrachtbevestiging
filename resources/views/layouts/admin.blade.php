<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>{{ isset($title) ? $title.' · Admin' : 'Admin' }}</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <div class="admin-shell">
        <header class="admin-topbar">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                Opdrachtbevestiging<span class="admin-brand-accent">.nl</span>
                <span class="admin-brand-tag">Admin</span>
            </a>

            <div class="admin-topbar-right">
                <a href="{{ route('dashboard') }}" class="admin-topbar-link">Naar app</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="admin-topbar-link admin-topbar-logout">Uitloggen</button>
                </form>
            </div>
        </header>

        <main class="admin-main">
            @yield('content')
        </main>
    </div>
</body>
</html>
