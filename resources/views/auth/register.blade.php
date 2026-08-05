@extends('layouts.app', [
    'title' => 'Account aanmaken | Opdrachtbevestiging.nl',
    'metaDescription' => 'Maak een account aan op Opdrachtbevestiging.nl en start direct met het aanmaken en versturen van opdrachtbevestigingen.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('register'),
    'mainClass' => 'auth-wrapper',
])

@section('content')
    @include('partials.auth.homepage-link')
    @include('partials.auth.brand')
    <section class="auth-card auth-card-register">
        <h1 class="form-title">Account aanmaken</h1>
        <p class="form-subtitle">Registreer je met je naam, e-mailadres en telefoonnummer.</p>

        @include('partials.forms.status')
        @include('partials.forms.errors')

        @php
            $step = 1;

            if ($errors->hasAny(['password', 'password_confirmation'])) {
                $step = 2;
            }
        @endphp

        <form method="POST" action="{{ route('register.store') }}" data-step-form data-initial-step="{{ $step }}">
            @csrf

            <div class="form-steps" aria-label="Registratiestappen">
                <div class="form-step-pill is-active" data-step-indicator="1">
                    <span class="form-step-number">1</span>
                    <span>Gegevens</span>
                </div>
                <div class="form-step-pill" data-step-indicator="2">
                    <span class="form-step-number">2</span>
                    <span>Wachtwoord</span>
                </div>
            </div>

            <section class="form-step-panel is-active" data-step-panel="1">
                <p class="form-step-title">Stap 1 van 2: jouw gegevens</p>

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

                <label for="email">E-mailadres</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>

                <label for="phone_number">Telefoonnummer</label>
                <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" required>

                <div class="actions actions-split">
                    <a href="{{ route('login') }}" class="btn btn-secondary">Ik heb al een account</a>
                    <button type="button" class="btn btn-primary" data-step-next>Volgende stap</button>
                </div>
            </section>

            <section class="form-step-panel" data-step-panel="2">
                <p class="form-step-title">Stap 2 van 2: wachtwoord instellen</p>

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
