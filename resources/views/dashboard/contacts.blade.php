@extends('layouts.dashboard', [
    'title' => 'Contacten',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Contacten',
        'title' => 'Opdrachtgevers en contactpersonen',
        'text' => 'Voeg een opdrachtgever toe en leg vast wie de opdrachtbevestiging per e-mail ontvangt.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <div class="dashboard-create-layout">
        @include('partials.dashboard.panel', [
            'title' => 'Nieuw contact toevoegen',
            'class' => 'dashboard-create-form-panel',
            'slot' => '
                <form method="POST" action="'.e(route('dashboard.contacts.store')).'" class="dashboard-form">
                    '.csrf_field().'

                    <label for="company_name">Bedrijfsnaam</label>
                    <input id="company_name" name="company_name" type="text" value="'.e(old('company_name')).'" required>

                    <div class="grid-2">
                        <div>
                            <label for="street_name">Straat</label>
                            <input id="street_name" name="street_name" type="text" value="'.e(old('street_name')).'" required>
                        </div>
                        <div>
                            <label for="house_number">Huisnummer</label>
                            <input id="house_number" name="house_number" type="text" value="'.e(old('house_number')).'" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="postal_code">Postcode</label>
                            <input id="postal_code" name="postal_code" type="text" value="'.e(old('postal_code')).'" required>
                        </div>
                        <div>
                            <label for="city">Plaats</label>
                            <input id="city" name="city" type="text" value="'.e(old('city')).'" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="contact_first_name">Voornaam contactpersoon</label>
                            <input id="contact_first_name" name="contact_first_name" type="text" value="'.e(old('contact_first_name')).'" required>
                        </div>
                        <div>
                            <label for="contact_last_name">Achternaam contactpersoon</label>
                            <input id="contact_last_name" name="contact_last_name" type="text" value="'.e(old('contact_last_name')).'" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="contact_email">E-mailadres contactpersoon</label>
                            <input id="contact_email" name="contact_email" type="email" value="'.e(old('contact_email')).'" required>
                        </div>
                        <div>
                            <label for="contact_phone">Telefoonnummer contactpersoon</label>
                            <input id="contact_phone" name="contact_phone" type="text" value="'.e(old('contact_phone')).'" required>
                        </div>
                    </div>

                    <div class="actions actions-end">
                        <button type="submit" class="btn btn-primary">Contact opslaan</button>
                    </div>
                </form>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'E-mailontvanger',
            'class' => 'dashboard-create-side-panel',
            'slot' => '
                <p>De contactpersoon van het gekozen bedrijf ontvangt de opdrachtbevestiging rechtstreeks per e-mail.</p>
                <p>De volledige opdrachtbevestiging staat in de e-mail zelf en wordt bij verzending ook als PDF meegestuurd.</p>
            ',
        ])
    </div>

    @include('partials.dashboard.panel', [
        'title' => 'Bestaande contacten',
        'class' => 'dashboard-panel-wide',
        'slot' => $contacts->isEmpty()
            ? '<p>Er zijn nog geen contacten toegevoegd.</p>'
            : view('partials.dashboard.contacts-table', ['contacts' => $contacts])->render(),
    ])
@endsection
