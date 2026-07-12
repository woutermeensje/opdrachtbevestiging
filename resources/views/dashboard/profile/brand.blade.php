@extends('layouts.dashboard', [
    'title' => 'Logo & Huisstijl',
])

@php
    $user = auth()->user();
    $primaryColor = old('primary_color', $user->primary_color ?: '#003B73');
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
                    <input id="primary_color" name="primary_color" type="color" value="{{ $primaryColor }}" required>
                </div>
                <div>
                    <label for="secondary_color">Secundaire kleur</label>
                    <input id="secondary_color" name="secondary_color" type="color" value="{{ $secondaryColor }}" required>
                </div>
            </div>
        </section>

        <div class="actions actions-end">
            <button type="submit" class="btn btn-primary">Logo & huisstijl opslaan</button>
        </div>
    </form>
@endsection
