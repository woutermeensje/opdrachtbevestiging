@extends('layouts.app', [
    'title' => 'Tarieven | Opdrachtbevestiging.nl',
    'metaDescription' => 'Bekijk de tarieven van Opdrachtbevestiging.nl: probeer 14 dagen gratis, kies maandelijks of neem het voordelige jaarabonnement.',
    'canonical' => route('pages.tariffs'),
    'mainClass' => 'tariffs-page',
])

@php
    $plans = [
        [
            'name' => '14 dagen gratis',
            'eyebrow' => 'Kennismaken',
            'price' => 'Gratis',
            'priceNote' => 'maximaal 2 opdrachtbevestigingen versturen',
            'description' => 'Ontdek rustig hoe je opdrachtbevestigingen aanmaakt, verstuurt en digitaal laat accorderen.',
            'features' => [
                'Maximaal 2 opdrachtbevestigingen versturen',
                'Incl. Kamer van Koophandel API',
                'Accordering trails',
                'Domein extensies',
                'Geen juridische kennis nodig',
            ],
            'cta' => 'Probeer gratis',
            'route' => route('register'),
            'featured' => false,
            'badge' => null,
        ],
        [
            'name' => 'Maandelijks',
            'eyebrow' => 'Flexibel starten',
            'price' => '€19,95',
            'priceNote' => 'excl. 21% btw per maand',
            'description' => 'Voor professionals die doorlopend opdrachtbevestigingen willen versturen zonder direct jaarlijks vast te leggen.',
            'features' => [
                'Onbeperkt opdrachtbevestigingen versturen',
                'Incl. Kamer van Koophandel API',
                'Accordering trails',
                'Domein extensies',
                'Geen juridische kennis nodig',
            ],
            'cta' => 'Kies maandelijks',
            'route' => route('register'),
            'featured' => false,
            'badge' => null,
        ],
        [
            'name' => 'Jaarlijks',
            'eyebrow' => 'Meeste voordeel',
            'price' => '€199',
            'priceNote' => 'excl. 21% btw per jaar',
            'description' => 'Voor wie het platform structureel gebruikt en voordeliger uit wil zijn dan maandelijks betalen.',
            'features' => [
                'Bespaar €40,40 per jaar',
                'Incl. Kamer van Koophandel API',
                'Accordering trails',
                'Domein extensies',
                'Geen juridische kennis nodig',
            ],
            'cta' => 'Kies jaarlijks',
            'route' => route('register'),
            'featured' => true,
            'badge' => 'Meeste voordeel',
        ],
    ];
@endphp

@section('content')
    <section class="tariffs-hero">
        <div class="container">
            <p class="page-eyebrow">Tarieven</p>
            <h1>Kies het pakket dat past bij je opdrachtflow</h1>
            <p class="tariffs-intro">Start gratis en stap daarna over op maandelijks of jaarlijks gebruik. Betaalde pakketten zijn exclusief 21% btw.</p>
        </div>
    </section>

    <section class="tariffs-content">
        <div class="container">
            <div class="tariffs-grid">
                @foreach ($plans as $plan)
                    <article class="tariff-plan{{ $plan['featured'] ? ' tariff-plan-featured' : '' }}">
                        @if ($plan['badge'])
                            <p class="tariff-badge">{{ $plan['badge'] }}</p>
                        @endif

                        <div class="tariff-plan-header">
                            <p class="tariff-plan-eyebrow">{{ $plan['eyebrow'] }}</p>
                            <h2>{{ $plan['name'] }}</h2>
                            <p class="tariff-plan-description">{{ $plan['description'] }}</p>
                        </div>

                        <div class="tariff-plan-price">
                            <strong>{{ $plan['price'] }}</strong>
                            <span>{{ $plan['priceNote'] }}</span>
                        </div>

                        <ul class="tariff-features">
                            @foreach ($plan['features'] as $feature)
                                <li>
                                    <span class="tariff-check">
                                        @include('partials.icons.icon', ['name' => 'check', 'size' => 18, 'strokeWidth' => 2.4])
                                    </span>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ $plan['route'] }}" class="btn {{ $plan['featured'] ? 'btn-primary' : 'btn-secondary' }} tariff-cta">{{ $plan['cta'] }}</a>
                    </article>
                @endforeach
            </div>

            <div class="tariffs-note">
                <p><strong>Jaarlijks is voordeliger.</strong> Twaalf maanden los kost €239,40 excl. btw. Met jaarlijks betaal je €199 excl. btw en houd je €40,40 voordeel.</p>
            </div>
        </div>
    </section>
@endsection
