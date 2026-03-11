@extends('layouts.dashboard', [
    'title' => 'Contacten',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Contacten',
        'title' => 'Opdrachtgevers en contactpersonen',
        'text' => 'Voeg bedrijven toe via de KVK API en leg per bedrijf vast wie de opdrachtbevestiging ontvangt en ondertekent.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <div class="dashboard-content-grid">
        @include('partials.dashboard.panel', [
            'title' => 'Nieuw contact toevoegen',
            'slot' => '
                <form method="POST" action="'.e(route('dashboard.contacts.store')).'" class="dashboard-form" data-kvk-form>
                    '.csrf_field().'

                    <div class="grid-kvk">
                        <div>
                            <label for="company_name">Bedrijfsnaam</label>
                            <input id="company_name" type="text" value="'.e(old('company_name')).'" data-company-name data-kvk-search-url="'.e(route('kvk.search')).'" list="contact-company-options" required>
                            <datalist id="contact-company-options" data-company-options></datalist>
                        </div>
                        <div class="dashboard-inline-actions">
                            <button type="button" class="btn btn-secondary" data-kvk-lookup data-kvk-url="'.e(route('kvk.lookup')).'">KVK-gegevens ophalen</button>
                        </div>
                    </div>

                    <p class="dashboard-kvk-feedback" data-kvk-feedback></p>

                    <label for="kvk_number">KVK-nummer</label>
                    <input id="kvk_number" name="kvk_number" type="text" value="'.e(old('kvk_number')).'" data-kvk-target="kvk_number" required readonly>

                    <div class="grid-2">
                        <div>
                            <label for="company_name_confirmed">Bedrijfsnaam</label>
                            <input id="company_name_confirmed" name="company_name" type="text" value="'.e(old('company_name')).'" data-kvk-target="company_name" required>
                        </div>
                        <div>
                            <label for="contact_email">E-mailadres contactpersoon</label>
                            <input id="contact_email" name="contact_email" type="email" value="'.e(old('contact_email')).'" required>
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

                    <div class="grid-3">
                        <div>
                            <label for="street_name">Straat</label>
                            <input id="street_name" name="street_name" type="text" value="'.e(old('street_name')).'" data-kvk-target="street_name">
                        </div>
                        <div>
                            <label for="house_number">Huisnummer</label>
                            <input id="house_number" name="house_number" type="text" value="'.e(old('house_number')).'" data-kvk-target="house_number">
                        </div>
                        <div>
                            <label for="house_number_addition">Toevoeging</label>
                            <input id="house_number_addition" name="house_number_addition" type="text" value="'.e(old('house_number_addition')).'" data-kvk-target="house_number_addition">
                        </div>
                    </div>

                    <div class="grid-3">
                        <div>
                            <label for="postal_code">Postcode</label>
                            <input id="postal_code" name="postal_code" type="text" value="'.e(old('postal_code')).'" data-kvk-target="postal_code">
                        </div>
                        <div>
                            <label for="city">Plaats</label>
                            <input id="city" name="city" type="text" value="'.e(old('city')).'" data-kvk-target="city">
                        </div>
                        <div>
                            <label for="country">Land</label>
                            <input id="country" name="country" type="text" value="'.e(old('country')).'" data-kvk-target="country">
                        </div>
                    </div>

                    <div class="actions actions-end">
                        <button type="submit" class="btn btn-primary">Contact opslaan</button>
                    </div>
                </form>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Signflow',
            'slot' => '
                <p>Signer 1 wordt de gebruiker zelf vanuit het platform. Signer 2 wordt de contactpersoon van het gekozen bedrijf.</p>
                <p>Deze contactenlijst vormt dus straks de bron voor de tweede ondertekenaar in Signhost.</p>
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
