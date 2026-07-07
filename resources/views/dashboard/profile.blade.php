@extends('layouts.dashboard', [
    'title' => 'Mijn profiel',
])

@php
    $user = auth()->user();
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Profiel',
        'title' => 'Mijn profiel',
        'text' => 'Beheer hier je persoonlijke gegevens en bedrijfsinformatie.',
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

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Gebruiker',
            'slot' => '
                <p><strong>Naam:</strong> '.e($user->first_name.' '.$user->last_name).'</p>
                <p><strong>E-mail:</strong> '.e($user->email).'</p>
                <p><strong>Telefoonnummer:</strong> '.e($user->phone_number ?: '-').'</p>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Bedrijfsgegevens',
            'class' => 'dashboard-panel-wide',
            'slot' => '
                <form method="POST" action="'.e(route('dashboard.profile.update')).'" class="dashboard-form" data-kvk-form>
                    '.csrf_field().'

                    <div class="grid-kvk">
                        <div>
                            <label for="company_name">Bedrijfsnaam</label>
                            <input id="company_name" type="text" value="'.e(old('company_name', $user->company_name)).'" data-company-name data-kvk-search-url="'.e(route('kvk.search')).'" list="profile-company-options" required>
                            <datalist id="profile-company-options" data-company-options></datalist>
                        </div>
                        <div class="dashboard-inline-actions">
                            <button type="button" class="btn btn-secondary" data-kvk-lookup data-kvk-url="'.e(route('kvk.lookup')).'">KVK-gegevens ophalen</button>
                        </div>
                    </div>

                    <p class="dashboard-kvk-feedback" data-kvk-feedback></p>

                    <label for="kvk_number">KVK-nummer</label>
                    <input id="kvk_number" name="kvk_number" type="text" value="'.e(old('kvk_number', $user->kvk_number)).'" data-kvk-target="kvk_number" required readonly>

                    <label for="company_name_confirmed">Bedrijfsnaam</label>
                    <input id="company_name_confirmed" name="company_name" type="text" value="'.e(old('company_name', $user->company_name)).'" data-kvk-target="company_name" required>

                    <div class="grid-3">
                        <div>
                            <label for="street_name">Straat</label>
                            <input id="street_name" name="street_name" type="text" value="'.e(old('street_name', $user->street_name)).'" data-kvk-target="street_name">
                        </div>
                        <div>
                            <label for="house_number">Huisnummer</label>
                            <input id="house_number" name="house_number" type="text" value="'.e(old('house_number', $user->house_number)).'" data-kvk-target="house_number">
                        </div>
                        <div>
                            <label for="house_number_addition">Toevoeging</label>
                            <input id="house_number_addition" name="house_number_addition" type="text" value="'.e(old('house_number_addition', $user->house_number_addition)).'" data-kvk-target="house_number_addition">
                        </div>
                    </div>

                    <div class="grid-3">
                        <div>
                            <label for="postal_code">Postcode</label>
                            <input id="postal_code" name="postal_code" type="text" value="'.e(old('postal_code', $user->postal_code)).'" data-kvk-target="postal_code">
                        </div>
                        <div>
                            <label for="city">Plaats</label>
                            <input id="city" name="city" type="text" value="'.e(old('city', $user->city)).'" data-kvk-target="city">
                        </div>
                        <div>
                            <label for="country">Land</label>
                            <input id="country" name="country" type="text" value="'.e(old('country', $user->country)).'" data-kvk-target="country">
                        </div>
                    </div>

                    <div class="actions actions-end">
                        <button type="submit" class="btn btn-primary">Bedrijfsgegevens opslaan</button>
                    </div>
                </form>
            ',
        ])
    </div>
@endsection
