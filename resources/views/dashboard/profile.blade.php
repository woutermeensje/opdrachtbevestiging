@extends('layouts.dashboard', [
    'title' => 'Mijn profiel',
])

@php
    $user = auth()->user();
    $defaultAgreements = \App\Models\Confirmation::sanitizeDescription(old('default_agreements', $user->default_agreements)) ?? '';
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Profiel',
        'title' => 'Mijn profiel',
        'text' => 'Beheer hier je persoonlijke gegevens, bedrijfsinformatie en vaste documentgegevens voor opdrachtbevestigingen.',
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

        <form method="POST" action="{{ route('dashboard.profile.update') }}" class="dashboard-form dashboard-panel-wide" enctype="multipart/form-data" data-kvk-form>
            @csrf

            <section class="dashboard-panel dashboard-panel-wide">
                <h2>Bedrijfsgegevens</h2>

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

            <section class="dashboard-panel dashboard-panel-wide">
                <h2>Vaste documentgegevens</h2>

                <div class="upload-block-grid">
                    <div class="upload-block">
                        <label for="company_logo">Bedrijfslogo</label>
                        <input id="company_logo" name="company_logo" type="file" accept=".png,.jpg,.jpeg">
                        <p class="form-help">PNG of JPG tot 4 MB.</p>
                        @if ($user->company_logo_original_name)
                            <p class="profile-file-current">Huidig bestand: {{ $user->company_logo_original_name }}</p>
                        @endif
                    </div>

                    <div class="upload-block">
                        <label for="terms">Algemene voorwaarden</label>
                        <input id="terms" name="terms" type="file" accept=".pdf,.doc,.docx">
                        <p class="form-help">PDF of Word-document tot 10 MB.</p>
                        @if ($user->terms_original_name)
                            <p class="profile-file-current">Huidig bestand: {{ $user->terms_original_name }}</p>
                        @endif
                    </div>
                </div>

                <div class="quill-field" data-quill-field>
                    <label for="default_agreements_editor">Basis afspraken</label>
                    <div id="default_agreements_editor" class="quill-editor" data-quill-editor data-quill-placeholder="Bijvoorbeeld betalingstermijnen of annuleringsvoorwaarden...">{!! $defaultAgreements !!}</div>
                    <textarea id="default_agreements" name="default_agreements" class="quill-editor-input" data-quill-input>{{ old('default_agreements', $user->default_agreements) }}</textarea>
                </div>
            </section>

            <div class="actions actions-end">
                <button type="submit" class="btn btn-primary">Profiel opslaan</button>
            </div>
        </form>
    </div>
@endsection
