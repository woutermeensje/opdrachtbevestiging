@extends('layouts.app')

@section('content')
    <section class="auth-card">
        <h1>Inloggen</h1>
        <p class="subtitle">Log in om je opdrachtbevestigingen te beheren.</p>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>

            <label for="password">Wachtwoord</label>
            <input id="password" name="password" type="password" required>

            <div class="actions">
                <a href="{{ route('register') }}" class="btn btn-secondary">Maak een account</a>
                <button type="submit" class="btn btn-primary">Inloggen</button>
            </div>
        </form>
    </section>
@endsection
