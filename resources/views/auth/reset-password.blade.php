@extends('layouts.app', [
    'title' => 'Nieuw wachtwoord instellen | Opdrachtbevestiging.nl',
    'metaDescription' => 'Stel een nieuw wachtwoord in voor je account op Opdrachtbevestiging.nl.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('password.reset', ['token' => $token, 'email' => $email]),
    'mainClass' => 'auth-wrapper',
])

@section('content')
    <section class="auth-card">
        <h1>Nieuw wachtwoord instellen</h1>
        <p class="subtitle">Kies een nieuw wachtwoord voor je account.</p>

        @include('partials.forms.status')
        @include('partials.forms.errors')

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required>

            <label for="password">Nieuw wachtwoord</label>
            <input id="password" name="password" type="password" required>

            <label for="password_confirmation">Herhaal nieuw wachtwoord</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>

            <div class="actions">
                <a href="{{ route('login') }}" class="btn btn-secondary">Terug naar inloggen</a>
                <button type="submit" class="btn btn-primary">Wachtwoord opslaan</button>
            </div>
        </form>
    </section>
@endsection
