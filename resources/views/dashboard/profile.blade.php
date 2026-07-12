@extends('layouts.dashboard', [
    'title' => 'Mijn account',
])

@php
    $user = auth()->user();
    $displayName = $user->name ?: trim($user->first_name.' '.$user->last_name);
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Profiel',
        'title' => 'Mijn account',
        'text' => 'Beheer je gebruikersnaam, contactgegevens en wachtwoord.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('dashboard.profile.account.update') }}" class="dashboard-form dashboard-profile-form">
        @csrf

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Accountgegevens</h2>

            <label for="name">Gebruikersnaam</label>
            <input id="name" name="name" type="text" value="{{ old('name', $displayName) }}" required>

            <div class="grid-2">
                <div>
                    <label for="first_name">Voornaam</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" required>
                </div>
                <div>
                    <label for="last_name">Achternaam</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" required>
                </div>
            </div>

            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>

            <label for="phone_number">Telefoonnummer</label>
            <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number', $user->phone_number) }}" required>
        </section>

        <div class="actions actions-end">
            <button type="submit" class="btn btn-primary">Accountgegevens opslaan</button>
        </div>
    </form>

    <form method="POST" action="{{ route('dashboard.profile.password.update') }}" class="dashboard-form dashboard-profile-form">
        @csrf

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Wachtwoord</h2>

            <label for="current_password">Huidig wachtwoord</label>
            <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>

            <label for="password">Nieuw wachtwoord</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>

            <label for="password_confirmation">Herhaal nieuw wachtwoord</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </section>

        <div class="actions actions-end">
            <button type="submit" class="btn btn-secondary">Wachtwoord wijzigen</button>
        </div>
    </form>
@endsection
