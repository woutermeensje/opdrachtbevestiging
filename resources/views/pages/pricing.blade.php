@extends('layouts.page', [
    'title' => 'Prijzen',
    'eyebrow' => 'Tarieven',
    'heading' => 'Prijzen',
    'intro' => 'Kies een pakket dat past bij je organisatie. Start eenvoudig met een vast jaarbedrag of neem contact op voor een corporate inrichting.',
])

@section('page-content')
    @include('partials.pages.content-card', [
        'class' => 'pricing-card pricing-card-featured',
        'title' => 'Basis account',
        'slot' => '
            <p class="pricing-kicker">Voor zzp, kleinbedrijf en teams die snel professioneel willen starten.</p>
            <p class="pricing-amount">€149 <span>excl. 21% btw per jaar</span></p>
            <ul class="pricing-list">
                <li>Eigen accountomgeving</li>
                <li>Opdrachtbevestigingen aanmaken en versturen</li>
                <li>Gegevens invullen zoals tarief, duur en voorwaarden</li>
                <li>Opdrachtgever toevoegen via de Kamer van Koophandel API</li>
            </ul>
            <a href="'.e(route('register')).'" class="btn btn-primary">Start met een basis account</a>
        ',
    ])

    @include('partials.pages.content-card', [
        'class' => 'pricing-card',
        'title' => 'Corporate',
        'slot' => '
            <p class="pricing-kicker">Voor organisaties met meerdere gebruikers, interne processen of specifieke wensen.</p>
            <p class="pricing-amount">Prijs op aanvraag</p>
            <ul class="pricing-list">
                <li>Geschikt voor grotere organisaties en corporate teams</li>
                <li>Afstemming op workflow, governance en inrichting</li>
                <li>Mogelijkheid tot aanvullende afspraken en maatwerk</li>
                <li>Persoonlijk contact over implementatie en gebruik</li>
            </ul>
            <a href="'.e(route('pages.contact')).'" class="btn btn-secondary">Vraag een voorstel aan</a>
        ',
    ])

    @include('partials.pages.content-card', [
        'class' => 'pricing-card pricing-card-wide',
        'title' => 'Welke optie past het best?',
        'slot' => '
            <p>Het <strong>Basis account</strong> is bedoeld voor organisaties die zelfstandig aan de slag willen met een vaste, overzichtelijke prijs.</p>
            <p>De <strong>Corporate</strong>-optie is bedoeld voor organisaties die meer afstemming nodig hebben over inrichting, proces, gebruikers of aanvullende eisen.</p>
            <p>Twijfel je welke vorm past? Neem dan contact op via de contactpagina, dan kan de juiste opzet worden afgestemd.</p>
        ',
    ])
@endsection
