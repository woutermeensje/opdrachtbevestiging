@extends('layouts.app', [
    'title' => 'Tarieven | Opdrachtbevestiging.nl',
    'metaDescription' => 'Bekijk de tarieven van Opdrachtbevestiging.nl: Freelancer, Business en Maatwerk voor professioneel opdrachtbevestigingen versturen.',
    'canonical' => route('pages.tariffs'),
    'mainClass' => 'tariffs-page',
])

@php
    $plans = [
        [
            'name' => 'Freelancer',
            'eyebrow' => 'Voor zelfstandigen',
            'price' => '€275',
            'priceNote' => 'excl. 21% btw per jaar',
            'description' => "Voor zzp'ers die opdrachtbevestigingen snel, zorgvuldig en professioneel willen vastleggen.",
            'features' => [
                'Onbeperkt opdrachtbevestigingen aanmaken',
                'Opdrachtgevers en contactpersonen beheren',
                'Digitaal akkoord per e-mail',
                'PDF-opdrachtbevestiging met eigen bedrijfsgegevens',
                'Bijlagen en offertes meesturen',
            ],
            'cta' => 'Start als freelancer',
            'route' => route('register'),
            'featured' => false,
        ],
        [
            'name' => 'Business',
            'eyebrow' => 'Voor groeiende teams',
            'price' => '€475',
            'priceNote' => 'excl. 21% btw per jaar',
            'description' => 'Voor bedrijven die vaker opdrachten bevestigen en meer grip willen op afspraken, status en documenten.',
            'features' => [
                'Alles uit Freelancer',
                'Professionele offerte- en opdrachtflow',
                'Eigen huisstijl en logo op documenten',
                'Vaste afspraken en algemene voorwaarden beheren',
                'Geschikt voor structureel gebruik binnen je organisatie',
            ],
            'cta' => 'Kies Business',
            'route' => route('register'),
            'featured' => true,
        ],
        [
            'name' => 'Maatwerk',
            'eyebrow' => 'Voor specifieke wensen',
            'price' => 'Op offerte',
            'priceNote' => 'afgestemd op inrichting en gebruik',
            'description' => 'Voor organisaties met extra wensen rond processen, inrichting, templates of ondersteuning.',
            'features' => [
                'Advies over inrichting en workflow',
                'Afstemming op interne processen',
                'Mogelijkheid tot aanvullende documenttemplates',
                'Ondersteuning bij implementatie',
                'Voorstel op basis van jouw situatie',
            ],
            'cta' => 'Vraag offerte aan',
            'route' => route('pages.contact'),
            'featured' => false,
        ],
    ];
@endphp

@section('content')
    <section class="tariffs-hero">
        <div class="container">
            <p class="page-eyebrow">Tarieven</p>
            <h1>Kies het pakket dat past bij je opdrachtflow</h1>
            <p class="tariffs-intro">Werk met duidelijke afspraken, professionele documenten en digitale akkoordroutes. Alle prijzen zijn jaarprijzen exclusief 21% btw.</p>
        </div>
    </section>

    <section class="tariffs-content">
        <div class="container">
            <div class="tariffs-grid">
                @foreach ($plans as $plan)
                    <article class="tariff-plan{{ $plan['featured'] ? ' tariff-plan-featured' : '' }}">
                        @if ($plan['featured'])
                            <p class="tariff-badge">Meest gekozen</p>
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
                <p><strong>Alle pakketten worden per 12 maanden gefactureerd.</strong> Twijfel je tussen Business en Maatwerk? Dan is Maatwerk handig wanneer je vooraf proces- of templatewensen wilt afstemmen.</p>
            </div>
        </div>
    </section>
@endsection
