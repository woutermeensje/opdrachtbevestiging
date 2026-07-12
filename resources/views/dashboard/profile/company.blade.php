@extends('layouts.dashboard', [
    'title' => 'Bedrijfsgegevens',
])

@php
    $user = auth()->user();
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Profiel',
        'title' => 'Bedrijfsgegevens',
        'text' => 'Beheer de bedrijfs- en adresgegevens die op je opdrachtbevestigingen worden gebruikt.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    @unless ($user->hasCompletedCompanyProfile())
        <div class="dashboard-notice dashboard-notice-warning">
            Vul je bedrijfsgegevens hieronder in. Pas daarna kun je opdrachtbevestigingen aanmaken en versturen.
        </div>
    @endunless

    <form method="POST" action="{{ route('dashboard.profile.company.update') }}" class="dashboard-form" data-kvk-form>
        @csrf

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Bedrijfsgegevens</h2>

            <h3>KVK-gegevens</h3>

            <div class="grid-kvk">
                <div>
                    <label for="company_name">Bedrijfsnaam</label>
                    <input id="company_name" type="text" value="{{ old('company_name', $user->company_name) }}" data-company-name data-kvk-search-url="{{ route('kvk.search') }}" list="profile-company-options" required>
                    <datalist id="profile-company-options" data-company-options></datalist>
                </div>
                <div class="dashboard-inline-actions">
                    <button type="button" class="btn btn-secondary" data-kvk-lookup data-kvk-url="{{ route('kvk.lookup') }}">KVK-gegevens ophalen</button>
                </div>
            </div>

            <p class="dashboard-kvk-feedback" data-kvk-feedback></p>

            <label for="kvk_number">KVK-nummer</label>
            <input id="kvk_number" name="kvk_number" type="text" value="{{ old('kvk_number', $user->kvk_number) }}" data-kvk-target="kvk_number" required readonly>

            <label for="company_name_confirmed">Bedrijfsnaam</label>
            <input id="company_name_confirmed" name="company_name" type="text" value="{{ old('company_name', $user->company_name) }}" data-kvk-target="company_name" required>

            <h3>Adresgegevens</h3>

            <div class="grid-3">
                <div>
                    <label for="street_name">Straat</label>
                    <input id="street_name" name="street_name" type="text" value="{{ old('street_name', $user->street_name) }}" data-kvk-target="street_name">
                </div>
                <div>
                    <label for="house_number">Huisnummer</label>
                    <input id="house_number" name="house_number" type="text" value="{{ old('house_number', $user->house_number) }}" data-kvk-target="house_number">
                </div>
                <div>
                    <label for="house_number_addition">Toevoeging</label>
                    <input id="house_number_addition" name="house_number_addition" type="text" value="{{ old('house_number_addition', $user->house_number_addition) }}" data-kvk-target="house_number_addition">
                </div>
            </div>

            <div class="grid-3">
                <div>
                    <label for="postal_code">Postcode</label>
                    <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $user->postal_code) }}" data-kvk-target="postal_code">
                </div>
                <div>
                    <label for="city">Plaats</label>
                    <input id="city" name="city" type="text" value="{{ old('city', $user->city) }}" data-kvk-target="city">
                </div>
                <div>
                    <label for="country">Land</label>
                    <input id="country" name="country" type="text" value="{{ old('country', $user->country) }}" data-kvk-target="country">
                </div>
            </div>
        </section>

        <div class="actions actions-end">
            <button type="submit" class="btn btn-primary">Bedrijfsgegevens opslaan</button>
        </div>
    </form>
@endsection
