@extends('layouts.app', [
    'title' => 'Wachtwoord vergeten | Opdrachtbevestiging.nl',
    'metaDescription' => 'Vraag een wachtwoord resetlink aan voor je account op Opdrachtbevestiging.nl.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('password.request'),
    'mainClass' => 'auth-wrapper',
])

@section('content')
    <section class="auth-card">
        <h1>Wachtwoord vergeten</h1>
        <p class="subtitle">Vul je e-mailadres in en we sturen je een link om een nieuw wachtwoord in te stellen.</p>

        @include('partials.forms.status')
        @include('partials.forms.errors')

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>

            <div class="actions">
                <a href="{{ route('login') }}" class="btn btn-secondary">Terug naar inloggen</a>
                <button type="submit" class="btn btn-primary">Resetlink versturen</button>
            </div>
        </form>
    </section>
@endsection
