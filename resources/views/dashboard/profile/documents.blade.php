@extends('layouts.dashboard', [
    'title' => 'Vaste documentgegevens',
])

@php
    $user = auth()->user();
    $defaultAgreements = \App\Models\Confirmation::sanitizeDescription(old('default_agreements', $user->default_agreements)) ?? '';
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Profiel',
        'title' => 'Vaste documentgegevens',
        'text' => 'Beheer je logo, algemene voorwaarden en vaste afspraken voor nieuwe opdrachtbevestigingen.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('dashboard.profile.documents.update') }}" class="dashboard-form" enctype="multipart/form-data">
        @csrf

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Documenten</h2>

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
        </section>

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Basis afspraken</h2>

            <div class="quill-field" data-quill-field>
                <label for="default_agreements_editor">Basis afspraken</label>
                <div id="default_agreements_editor" class="quill-editor" data-quill-editor data-quill-placeholder="Bijvoorbeeld betalingstermijnen of annuleringsvoorwaarden...">{!! $defaultAgreements !!}</div>
                <textarea id="default_agreements" name="default_agreements" class="quill-editor-input" data-quill-input>{{ old('default_agreements', $user->default_agreements) }}</textarea>
            </div>
        </section>

        <div class="actions actions-end">
            <button type="submit" class="btn btn-primary">Documentgegevens opslaan</button>
        </div>
    </form>
@endsection
