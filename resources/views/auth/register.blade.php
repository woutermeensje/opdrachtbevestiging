@extends('layouts.app')

@section('content')
    <section class="auth-card">
        <h1>Account aanmaken</h1>
        <p class="subtitle">Registreer je met je naam, e-mailadres en bedrijfsnaam.</p>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

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

            <label for="company_name">Bedrijfsnaam</label>
            <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" required>

            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>

            <label for="password">Wachtwoord</label>
            <input id="password" name="password" type="password" required>

            <label for="password_confirmation">Herhaal wachtwoord</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>

            <div class="actions">
                <a href="{{ route('login') }}" class="btn btn-secondary">Ik heb al een account</a>
                <button type="submit" class="btn btn-primary">Registreren</button>
            </div>
        </form>
    </section>
@endsection
