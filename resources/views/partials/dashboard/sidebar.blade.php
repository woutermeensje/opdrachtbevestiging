@php
    $items = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'dashboard.create', 'label' => 'Aanmaken', 'icon' => 'plus-square'],
        ['route' => 'dashboard.confirmations', 'label' => 'Opdrachtbevestigingen', 'icon' => 'file-text'],
        ['route' => 'dashboard.profile', 'label' => 'Mijn profiel', 'icon' => 'user-circle'],
    ];
@endphp

<aside class="dashboard-sidebar">
    <a href="{{ route('dashboard') }}" class="dashboard-brand">
        <img src="{{ asset('Logo/Logo-opdrachtbevesting.png') }}" alt="Opdrachtbevestiging.nl" class="dashboard-brand-logo">
    </a>

    <nav class="dashboard-nav" aria-label="Dashboard navigatie">
        @foreach ($items as $item)
            <a
                href="{{ route($item['route']) }}"
                class="dashboard-nav-link{{ request()->routeIs($item['route']) ? ' is-active' : '' }}"
            >
                <span class="dashboard-nav-icon">
                    @include('partials.icons.icon', ['name' => $item['icon']])
                </span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="dashboard-logout">
        @csrf
        <button type="submit" class="dashboard-logout-button">
            <span class="dashboard-nav-icon">
                @include('partials.icons.icon', ['name' => 'log-out'])
            </span>
            <span>Uitloggen</span>
        </button>
    </form>
</aside>
