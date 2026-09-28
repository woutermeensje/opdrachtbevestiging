@php
    $title = 'Opdrachtbevestiging.nl | Eenvoudig afspraken vastleggen';
    $metaDescription = 'Bekijk onze (gratis) tool om eenvoudig een opdrachtbevestiging op te stellen en te versturen naar jouw opdrachtgever.';

    $features = [
        'Incl. Kamer van Koophandel API',
        'Accordering trails',
        'Domein extensies',
        'Geen juridische kennis nodig',
    ];
@endphp

@extends('layouts.marketing', [
    'title' => $title,
    'metaDescription' => $metaDescription,
    'canonical' => route('home'),
    'structuredData' => json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => route('home').'#website',
                'url' => route('home'),
                'name' => 'Opdrachtbevestiging.nl',
                'inLanguage' => 'nl-NL',
            ],
            [
                '@type' => 'WebPage',
                '@id' => route('home'),
                'url' => route('home'),
                'name' => $title,
                'description' => $metaDescription,
                'isPartOf' => ['@id' => route('home').'#website'],
                'inLanguage' => 'nl-NL',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
])

@section('content')
    <div class="mk-home">
        <section class="mk-hero" aria-labelledby="mk-hero-title">
            <div class="mk-hero__inner">
                <div class="mk-hero__content">
                    <span class="mk-hero__eyebrow">Opdrachtbevestiging.nl</span>
                    <h1 id="mk-hero-title" class="mk-hero__title"><span class="mk-hero__title-highlight">Opdrachtbevestigingen</span> versturen en beheren!</h1>
                    <p class="mk-hero__subtitle">Stel eenvoudig een professionele opdrachtbevestiging op die jouw klant binnen een paar seconden per e-mail kan accorderen.</p>

                    <div class="mk-hero__actions">
                        <a href="{{ route('register') }}" class="mk-btn mk-btn--primary">Gratis proberen</a>
                        <a href="{{ route('pages.tariffs') }}" class="mk-btn mk-btn--outline">Wat kost het</a>
                    </div>

                    <ul class="mk-hero__features">
                        @foreach ($features as $feature)
                            <li class="mk-hero__feature">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    </div>

    <section class="mk-section" aria-labelledby="mk-types-title">
        <h2 id="mk-types-title" class="mk-section__title">Alle typen bevestigingen:</h2>

        @include('partials.marketing.opstellen-directory')
    </section>

    <section class="mk-section">
        <div class="mk-card mk-card--media">
            <img src="{{ asset('images/home/dashboard.png') }}" alt="Dashboard - Opdrachtbevestigingen bekijken en beheren." width="1536" height="731" loading="lazy" decoding="async">
        </div>
    </section>

    <section class="mk-section">
        <div class="mk-card mk-card--padded mk-prose">
            <h2>Opdrachtbevestiging.nl</h2>
            <p>Wat leuk dat je opdrachtbevestiging.nl hebt gevonden. Wij zijn een software tool die je kan gebruiken om snel en eenvoudige afspraken met jouw opdrachtgevers vast te leggen.</p>

            <h2>Wanneer interessant?</h2>
            <p>Je kent het wel. Een opdrachtgever heeft een verzoek, opdracht of project. Om voor iedere opdracht een complete overeenkomst door een jurist te laten opstellen, is vaak te veel gedoe. Toch vragen veel van dit soort situaties erom dat afspraken vooraf duidelijk worden vastgelegd.</p>
            <p>Denk bijvoorbeeld aan uurtarieven, afspraken over meerwerk, de omvang van de werkzaamheden, reiskostenvergoedingen, reistijdvergoedingen, de duur van de werkzaamheden en eventuele resultaatsverplichtingen. Allemaal zaken die op het eerste gezicht duidelijk lijken, maar waar achteraf toch discussie over kan ontstaan.</p>
            <p>Met een eenvoudige opdrachtbevestiging kun je deze problemen voorkomen.</p>

            <h2>Hoe werkt het?</h2>
            <p>Je maakt een account aan, maakt een opdrachtbevestiging aan, zet daarin alle afspraken en verstuur deze naar jouw opdrachtgever. In jouw account kan je dan al jouw opdrachtbevestigingen terugvinden, beheren en eventueel bewerken.</p>

            <h2>Wat zijn opdrachtbevestigingen?</h2>
            <p>Opdrachtbevestigingen zijn eigenlijk mini-overeenkomsten die vaak per e-mail of per document worden vastgelegd. Ze zijn handig omdat ze niet de uitgebreide juridische structuur vereisen van een overeenkomst, maar toch juridisch bindend zijn. Omdat de belangrijkste afspraken zwart op wit zijn vastgelegd.</p>
            <p>In de praktijk zijn opdrachtbevestigingen vaak niets anders dan de offerte voor de klant met daaronder een handtekening. Van zowel de klant als de opdrachtnemers.</p>
            <p>Andere termen die ook worden gebruikt zijn onder meer:</p>
            <ul>
                <li>Opdrachtbon</li>
                <li>Projectbevestiging</li>
                <li>Orderbevestiging</li>
                <li>Plaatsingsbevestiging</li>
                <li>Inkooporder</li>
            </ul>

            <h2>Waarom Opdrachtbevestiging.nl?</h2>
            <p>Het probleem met veel opdrachtbevestigingen is dat ze geen mooie vorm en opmaak hebben. En vaak ook niet aan een bepaalde structuur voldoen. Vaak is een opdrachtbevestiging een eenvoudige e-mail die is verzonden met daarin de belangrijkste voorwaarden opgesomd. Opdrachtbevestiging moet hierin verandering brengen. Jouw opdrachtbevestigingen worden voorzien van een professionele opmaak, worden volgens een standaard protocol verzonden en het akkoord van jouw opdrachtgever is juridisch bindend.</p>

            <h2>Voor wie is Opdrachtbevestiging.nl bedoelt?</h2>
            <p>Deze tool is ontwikkeld voor het Nederlandse MKB (Midden- en Kleinbedrijf). Met als doel om de dagelijkse afspraken die worden gemaakt door bijvoorbeeld bouw- en montagebedrijven, schildersbedrijven, uitzendbureaus, verhuisbedrijven, verhuurbedrijven en zakelijke dienstverleners zoals financieel adviesbureaus, fiscalisten en adviesbedrijven.</p>

            <h2>Voorbeeld</h2>
            <p>Stel een uitzendbureau heeft een inlener die wekelijks nieuwe uitzendkracht inhuurt. Het uitzendbureau heeft een algemene overeenkomst met de opdrachtgever waar allerlei zaken in zijn afgesproken. Toch is het zo dat met iedere nieuwe uitzendkracht die het uitzendbureau plaatst, er een ander bruto uurloon, cao schaal en uurtarief van toepassing is. Het uitzendbureau zal dus bij iedere nieuwe plaatsing een opdrachtbevestiging willen opstellen samen met de opdrachtgever. Waarin dit allemaal per uitzendkracht wordt vastgelegd.</p>
        </div>
    </section>
@endsection
