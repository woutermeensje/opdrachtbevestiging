@extends('layouts.dashboard', [
    'title' => 'Opdrachtgever toevoegen',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Opdrachtgevers',
        'title' => 'Opdrachtgever toevoegen',
        'text' => 'Voeg een opdrachtgever toe en leg vast wie de opdrachtbevestiging per e-mail ontvangt.',
    ])

    @include('partials.forms.errors')

    <div class="dashboard-create-layout">
        <section class="dashboard-panel dashboard-create-form-panel">
            <h2>Nieuwe opdrachtgever</h2>

            <form method="POST" action="{{ route('dashboard.contacts.store') }}" class="dashboard-form">
                @csrf

                <div class="profile-form-section">
                    <h3>Bedrijfsgegevens</h3>

                    <label for="company_name">Bedrijfsnaam</label>
                    <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" required>
                </div>

                <div class="profile-form-section">
                    <h3>Adresgegevens</h3>

                    <div class="grid-2">
                        <div>
                            <label for="street_name">Straat</label>
                            <input id="street_name" name="street_name" type="text" value="{{ old('street_name') }}" required>
                        </div>
                        <div>
                            <label for="house_number">Huisnummer</label>
                            <input id="house_number" name="house_number" type="text" value="{{ old('house_number') }}" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="postal_code">Postcode</label>
                            <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code') }}" required>
                        </div>
                        <div>
                            <label for="city">Plaats</label>
                            <input id="city" name="city" type="text" value="{{ old('city') }}" required>
                        </div>
                    </div>
                </div>

                <div class="profile-form-section">
                    <h3>Contactgegevens</h3>

                    <div class="grid-2">
                        <div>
                            <label for="contact_first_name">Voornaam</label>
                            <input id="contact_first_name" name="contact_first_name" type="text" value="{{ old('contact_first_name') }}" required>
                        </div>
                        <div>
                            <label for="contact_last_name">Achternaam</label>
                            <input id="contact_last_name" name="contact_last_name" type="text" value="{{ old('contact_last_name') }}" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="contact_email">E-mailadres</label>
                            <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email') }}" required>
                        </div>
                        <div>
                            <label for="contact_phone">Telefoonnummer</label>
                            <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone') }}" required>
                        </div>
                    </div>
                </div>

                <div class="actions actions-end">
                    <button type="submit" class="btn btn-primary">Opdrachtgever opslaan</button>
                </div>
            </form>
        </section>

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
