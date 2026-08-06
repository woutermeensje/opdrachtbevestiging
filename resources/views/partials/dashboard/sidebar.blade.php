@php
    $items = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        [
            'route' => 'dashboard.quotes',
            'label' => 'Offertes',
            'icon' => 'file-plus',
            'children' => [
                ['route' => 'dashboard.quotes', 'label' => 'Mijn offertes'],
                ['route' => 'dashboard.quotes.create', 'label' => 'Offerte aanmaken'],
            ],
        ],
        [
            'route' => 'dashboard.confirmations',
            'label' => 'Opdrachtbevestigingen',
            'icon' => 'file-text',
            'children' => [
                ['route' => 'dashboard.create', 'label' => 'Opdrachtbevestiging toevoegen'],
            ],
        ],
        [
            'route' => 'dashboard.contacts',
            'label' => 'Opdrachtgevers',
            'icon' => 'building',
            'children' => [
                ['route' => 'dashboard.contacts.create', 'label' => 'Opdrachtgever toevoegen'],
            ],
        ],
        [
            'route' => 'dashboard.profile',
            'label' => 'Mijn profiel',
            'icon' => 'user-circle',
            'children' => [
                ['route' => 'dashboard.profile', 'label' => 'Mijn account'],
                ['route' => 'dashboard.profile.company', 'label' => 'Bedrijfsgegevens'],
                ['route' => 'dashboard.profile.documents', 'label' => 'Documenten'],
                ['route' => 'dashboard.profile.agreements', 'label' => 'Basis afspraken'],
                ['route' => 'dashboard.profile.brand', 'label' => 'Logo & Huisstijl'],
            ],
        ],
        ['route' => 'billing.show', 'label' => 'Abonnement', 'icon' => 'credit-card'],
    ];
@endphp

<aside class="dashboard-sidebar">
    <a href="{{ route('dashboard') }}" class="dashboard-brand">
        <span class="dashboard-brand-text">Opdrachtbevestiging<span class="dashboard-brand-text-accent">.nl</span></span>
    </a>

    <nav class="dashboard-nav" aria-label="Dashboard navigatie">
        @foreach ($items as $item)
            @php
                $childRoutes = collect($item['children'] ?? [])->pluck('route')->all();
                $isGroupActive = request()->routeIs($item['route']) || ($childRoutes !== [] && request()->routeIs(...$childRoutes));
            @endphp
            <div class="dashboard-nav-group">
                <a
                    href="{{ route($item['route']) }}"
                    class="dashboard-nav-link{{ $isGroupActive ? ' is-active' : '' }}"
                >
                    <span class="dashboard-nav-icon">
                        @include('partials.icons.icon', ['name' => $item['icon']])
                    </span>
                    <span>{{ $item['label'] }}</span>
                </a>

                @if (!empty($item['children']))
                    <div class="dashboard-nav-children">
                        @foreach ($item['children'] as $child)
                            <a
                                href="{{ route($child['route']) }}"
                                class="dashboard-nav-sublink{{ request()->routeIs($child['route']) ? ' is-active' : '' }}"
                            >
                                <span>{{ $child['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
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
