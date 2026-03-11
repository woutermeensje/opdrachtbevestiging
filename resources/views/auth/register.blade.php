@extends('layouts.app', [
    'title' => 'Account aanmaken | Opdrachtbevestiging.nl',
    'metaDescription' => 'Maak een account aan op Opdrachtbevestiging.nl en start direct met het aanmaken en versturen van opdrachtbevestigingen.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('register'),
    'mainClass' => 'auth-wrapper',
])

@section('content')
    <section class="auth-card">
        <h1>Account aanmaken</h1>
        <p class="subtitle">Registreer je met je naam, e-mailadres en bedrijfsnaam.</p>

        @include('partials.forms.status')
        @include('partials.forms.errors')

        @php
            $step = 1;

            if ($errors->hasAny(['kvk_number', 'company_name', 'street_name', 'house_number', 'house_number_addition', 'postal_code', 'city', 'country'])) {
                $step = 2;
            }

            if ($errors->hasAny(['email', 'password', 'password_confirmation'])) {
                $step = 3;
            }
        @endphp

        <form method="POST" action="{{ route('register.store') }}" data-kvk-form data-step-form data-initial-step="{{ $step }}">
            @csrf

            <div class="form-steps" aria-label="Registratiestappen">
                <div class="form-step-pill is-active" data-step-indicator="1">
                    <span class="form-step-number">1</span>
                    <span>Persoon</span>
                </div>
                <div class="form-step-pill" data-step-indicator="2">
                    <span class="form-step-number">2</span>
                    <span>Bedrijf</span>
                </div>
                <div class="form-step-pill" data-step-indicator="3">
                    <span class="form-step-number">3</span>
                    <span>Account</span>
                </div>
            </div>

            <section class="form-step-panel is-active" data-step-panel="1">
                <p class="form-step-title">Stap 1 van 3: jouw gegevens</p>

                <div class="grid-2">
                    <div>
                        <label for="first_name">Voornaam</label>
                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required>
                    </div>
                    <div>
                        <label for="last_name">Achternaam</label>
                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="actions actions-split">
                    <a href="{{ route('login') }}" class="btn btn-secondary">Ik heb al een account</a>
                    <button type="button" class="btn btn-primary" data-step-next>Volgende stap</button>
                </div>
            </section>

            <section class="form-step-panel" data-step-panel="2">
                <p class="form-step-title">Stap 2 van 3: bedrijfsgegevens</p>

                <div class="grid-kvk">
                    <div>
                        <label for="company_name">Bedrijfsnaam</label>
                        <input
                            id="company_name"
                            name="company_name"
                            type="text"
                            value="{{ old('company_name') }}"
                            data-company-name
                            data-kvk-search-url="{{ route('kvk.search') }}"
                            list="register-company-options"
                            required
                        >
                        <datalist id="register-company-options" data-company-options></datalist>
                    </div>
                    <div class="kvk-actions">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-kvk-lookup
                            data-kvk-url="{{ route('kvk.lookup') }}"
                        >
                            Bedrijfsgegevens ophalen
                        </button>
                    </div>
                </div>

                <p class="form-help" data-kvk-feedback></p>

                <label for="kvk_number">KVK-nummer</label>
                <input id="kvk_number" name="kvk_number" type="text" value="{{ old('kvk_number') }}" data-kvk-target="kvk_number" required readonly>

                <div class="grid-address">
                    <div>
                        <label for="street_name">Straat</label>
                        <input id="street_name" name="street_name" type="text" value="{{ old('street_name') }}" data-kvk-target="street_name" required>
                    </div>
                    <div>
                        <label for="house_number">Huisnummer</label>
                        <input id="house_number" name="house_number" type="text" value="{{ old('house_number') }}" data-kvk-target="house_number" required>
                    </div>
                </div>

                <div class="grid-address">
                    <div>
                        <label for="house_number_addition">Toevoeging</label>
                        <input id="house_number_addition" name="house_number_addition" type="text" value="{{ old('house_number_addition') }}" data-kvk-target="house_number_addition">
                    </div>
                    <div>
                        <label for="postal_code">Postcode</label>
                        <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code') }}" data-kvk-target="postal_code" required>
                    </div>
                </div>

                <div class="grid-address">
                    <div>
                        <label for="city">Plaats</label>
                        <input id="city" name="city" type="text" value="{{ old('city') }}" data-kvk-target="city" required>
                    </div>
                    <div>
                        <label for="country">Land</label>
                        <input id="country" name="country" type="text" value="{{ old('country', 'Nederland') }}" data-kvk-target="country" required>
                    </div>
                </div>

                <div class="actions actions-end">
                    <button type="button" class="btn btn-secondary" data-step-prev>Vorige stap</button>
                    <button type="button" class="btn btn-primary" data-step-next>Volgende stap</button>
                </div>
            </section>

            <section class="form-step-panel" data-step-panel="3">
                <p class="form-step-title">Stap 3 van 3: account instellen</p>

                <label for="email">E-mailadres</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>

                <label for="password">Wachtwoord</label>
                <input id="password" name="password" type="password" required>

                <label for="password_confirmation">Herhaal wachtwoord</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>

                <div class="actions actions-end">
                    <button type="button" class="btn btn-secondary" data-step-prev>Vorige stap</button>
                    <button type="submit" class="btn btn-primary">Registreren</button>
                </div>
            </section>
        </form>
    </section>
@endsection
