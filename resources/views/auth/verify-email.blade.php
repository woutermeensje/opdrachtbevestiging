@extends('layouts.app', [
    'title' => 'Bevestig je e-mailadres | Opdrachtbevestiging.nl',
    'metaDescription' => 'Bevestig je e-mailadres om je account op Opdrachtbevestiging.nl te activeren.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('verification.notice'),
    'mainClass' => 'auth-wrapper',
])

@section('content')
    <section class="auth-card">
        <h1 class="form-title">Bevestig je e-mailadres</h1>
        <p class="form-subtitle">We hebben een verificatielink gestuurd naar het e-mailadres van je account.</p>

        @include('partials.forms.status')
        @include('partials.forms.errors')

        <p>Open de e-mail en klik op de link om je account te activeren. Zonder bevestiging kun je het dashboard nog niet gebruiken.</p>

        <form method="POST" action="{{ route('verification.send') }}" class="auth-verification-form">
            @csrf
            <button type="submit" class="btn btn-primary">Verificatiemail opnieuw sturen</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="auth-switch-account">
            @csrf
            <span>Verkeerd e-mailadres gebruikt?</span>
            <button type="submit">Ander account gebruiken</button>
        </form>
    </section>
@endsection
