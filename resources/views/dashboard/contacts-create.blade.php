@extends('layouts.dashboard', [
    'title' => $contact ? 'Opdrachtgever bewerken' : 'Opdrachtgever toevoegen',
])

@php
    $isEditing = filled($contact);
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Opdrachtgevers',
        'title' => $isEditing ? 'Opdrachtgever bewerken' : 'Opdrachtgever toevoegen',
        'text' => $isEditing
            ? 'Wijzig de bedrijfs-, adres- en contactgegevens van deze opdrachtgever.'
            : 'Voeg een opdrachtgever toe en leg vast wie de opdrachtbevestiging per e-mail ontvangt.',
    ])

    @include('partials.forms.errors')

    <div class="dashboard-create-layout">
        <div class="dashboard-create-form-panel">
            <form method="POST" action="{{ $formAction }}" class="dashboard-form">
                @csrf
                @if ($formMethod !== 'POST')
                    @method($formMethod)
                @endif

                <section class="dashboard-panel dashboard-panel-wide">
                    <h2>Bedrijfsgegevens</h2>

                    <div class="grid-2">
                        <div>
                            <label for="company_name">Bedrijfsnaam</label>
                            <input id="company_name" name="company_name" type="text" value="{{ old('company_name', $contact?->company_name) }}" required>
                        </div>
                        <div>
                            <label for="kvk_number">KVK-nummer</label>
                            <input id="kvk_number" name="kvk_number" type="text" value="{{ old('kvk_number', $contact?->kvk_number) }}">
                        </div>
                    </div>
                </section>

                <section class="dashboard-panel dashboard-panel-wide">
                    <h2>Adresgegevens</h2>

                    <div class="grid-3">
                        <div>
                            <label for="street_name">Straat</label>
                            <input id="street_name" name="street_name" type="text" value="{{ old('street_name', $contact?->street_name) }}" required>
                        </div>
                        <div>
                            <label for="house_number">Huisnummer</label>
                            <input id="house_number" name="house_number" type="text" value="{{ old('house_number', $contact?->house_number) }}" required>
                        </div>
                        <div>
                            <label for="house_number_addition">Toevoeging</label>
                            <input id="house_number_addition" name="house_number_addition" type="text" value="{{ old('house_number_addition', $contact?->house_number_addition) }}">
                        </div>
                    </div>

                    <div class="grid-3">
                        <div>
                            <label for="postal_code">Postcode</label>
                            <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $contact?->postal_code) }}" required>
                        </div>
                        <div>
                            <label for="city">Plaats</label>
                            <input id="city" name="city" type="text" value="{{ old('city', $contact?->city) }}" required>
                        </div>
                        <div>
                            <label for="country">Land</label>
                            <input id="country" name="country" type="text" value="{{ old('country', $contact?->country) }}">
                        </div>
                    </div>
                </section>

                <section class="dashboard-panel dashboard-panel-wide">
                    <h2>Contactgegevens</h2>

                    <div class="grid-2">
                        <div>
                            <label for="contact_first_name">Voornaam</label>
                            <input id="contact_first_name" name="contact_first_name" type="text" value="{{ old('contact_first_name', $contact?->contact_first_name) }}" required>
                        </div>
                        <div>
                            <label for="contact_last_name">Achternaam</label>
                            <input id="contact_last_name" name="contact_last_name" type="text" value="{{ old('contact_last_name', $contact?->contact_last_name) }}" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="contact_email">E-mailadres</label>
                            <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $contact?->contact_email) }}" required>
                        </div>
                        <div>
                            <label for="contact_phone">Telefoonnummer</label>
                            <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $contact?->contact_phone) }}" required>
                        </div>
                    </div>
                </section>

                <div class="actions actions-split">
                    <a href="{{ route('dashboard.contacts') }}" class="btn btn-secondary">Annuleren</a>
                    <button type="submit" class="btn btn-primary">
                        {{ $isEditing ? 'Wijzigingen opslaan' : 'Opdrachtgever opslaan' }}
                    </button>
                </div>
            </form>
        </div>

        @include('partials.dashboard.panel', [
            'title' => 'E-mailontvanger',
            'class' => 'dashboard-create-side-panel',
            'slot' => '
                <p>De contactpersoon van het gekozen bedrijf ontvangt de opdrachtbevestiging rechtstreeks per e-mail.</p>
                <p>De volledige opdrachtbevestiging staat in de e-mail zelf en wordt bij verzending ook als PDF meegestuurd.</p>
            ',
        ])
    </div>
@endsection
