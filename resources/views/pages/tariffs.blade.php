@extends('layouts.app', [
    'title' => 'Tarieven | Opdrachtbevestiging.nl',
    'metaDescription' => 'Bekijk het tarief van Opdrachtbevestiging.nl: een jaarabonnement voor onbeperkt gebruik van het platform.',
    'canonical' => route('pages.tariffs'),
    'mainClass' => 'tariffs-wrapper',
])

@php
    $features = [
        'Verstuur onbeperkt opdrachtbevestigingen',
        'Gratis Kamer van Koophandel API',
        'Professionele inschrijfformulieren voor opdrachtgevers',
        'Eigen bedrijfsnaam.opdrachtbevestiging.nl domein extensie',
        'Accordering per e-mail!',
        'Eigen huisstijl toevoegen',
        'Uniek dashboard met opdrachtbevestigingen',
    ];
@endphp

@section('content')
    <section class="tariff-card">
        <p class="tariff-price">
            &euro;97,00 <span class="tariff-price-vat">excl. 21% btw</span>
        </p>
        <p class="tariff-period">Per 12 maanden*.</p>

        <ul class="tariff-features">
            @foreach ($features as $feature)
                <li>
                    <span class="tariff-check">
                        @include('partials.icons.icon', ['name' => 'check', 'size' => 18, 'strokeWidth' => 2.4])
                    </span>
                    <span>{{ $feature }}</span>
                </li>
            @endforeach
        </ul>

        <a href="{{ route('register') }}" class="btn btn-primary tariff-cta">14 dagen gratis</a>

        <p class="tariff-footnote">* Jaarlijks abonnement, per 12 maanden gefactureerd.</p>
    </section>
@endsection
