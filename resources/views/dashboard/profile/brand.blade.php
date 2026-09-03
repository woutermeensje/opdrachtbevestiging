@extends('layouts.dashboard', [
    'title' => 'Logo & Huisstijl',
])

@php
    $user = auth()->user();
    $primaryColor = old('primary_color', $user->primary_color);
    $secondaryColor = old('secondary_color', $user->secondary_color ?: '#FBFAF8');
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Profiel',
        'title' => 'Logo & Huisstijl',
        'text' => 'Beheer je logo en de kleuren van je huisstijl.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('dashboard.profile.brand.update') }}" class="dashboard-form dashboard-profile-form" enctype="multipart/form-data">
        @csrf

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Logo</h2>

            <div class="upload-block">
                <label for="company_logo">Bedrijfslogo</label>
                <input id="company_logo" name="company_logo" type="file" accept=".png,.jpg,.jpeg">
                <p class="form-help">PNG of JPG tot 4 MB.</p>
                @if ($user->company_logo_original_name)
                    <p class="profile-file-current">Huidig bestand: {{ $user->company_logo_original_name }}</p>
                @endif
            </div>
        </section>

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Huisstijl</h2>

            <div class="grid-2">
                <div>
                    <label for="primary_color">Primaire kleur</label>
                    <div class="color-field" data-color-field>
                        <span class="color-field-swatch" data-color-swatch></span>
                        <input
                            id="primary_color"
                            name="primary_color"
                            type="text"
                            value="{{ $primaryColor }}"
                            required
                            maxlength="7"
                            spellcheck="false"
                            autocomplete="off"
                            inputmode="text"
                            pattern="#[0-9A-Fa-f]{6}"
                            placeholder="#7C5CFA"
                            title="Voer een hex-kleurcode in, bijvoorbeeld #7C5CFA"
                            data-color-input
                            @if (! $primaryColor) data-theme-primary-color-default="true" @endif
                        >
                    </div>
                    <p class="form-help">Hex-kleurcode, bijvoorbeeld <code>#7C5CFA</code>.</p>
                </div>
                <div>
                    <label for="secondary_color">Secundaire kleur</label>
                    <div class="color-field" data-color-field>
                        <span class="color-field-swatch" data-color-swatch></span>
                        <input
                            id="secondary_color"
                            name="secondary_color"
                            type="text"
                            value="{{ $secondaryColor }}"
                            required
                            maxlength="7"
                            spellcheck="false"
                            autocomplete="off"
                            inputmode="text"
                            pattern="#[0-9A-Fa-f]{6}"
                            placeholder="#FBFAF8"
                            title="Voer een hex-kleurcode in, bijvoorbeeld #FBFAF8"
                            data-color-input
                        >
                    </div>
                    <p class="form-help">Hex-kleurcode, bijvoorbeeld <code>#FBFAF8</code>.</p>
                </div>
            </div>
        </section>

        <div class="actions actions-end">
            <button type="submit" class="btn btn-primary">Logo & huisstijl opslaan</button>
        </div>
    </form>
@endsection
