@extends('layouts.dashboard', [
    'title' => 'Documenten',
])

@php
    $user = auth()->user();
    $defaultAgreements = \App\Models\Confirmation::sanitizeDescription(old('default_agreements', $user->default_agreements)) ?? '';
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Profiel',
        'title' => 'Documenten',
        'text' => 'Beheer je algemene voorwaarden en vaste afspraken voor nieuwe opdrachtbevestigingen.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('dashboard.profile.documents.update') }}" class="dashboard-form dashboard-profile-form" enctype="multipart/form-data">
        @csrf

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Algemene voorwaarden</h2>

            <div class="upload-block">
                <label for="terms">Bestand</label>
                <input id="terms" name="terms" type="file" accept=".pdf,.doc,.docx">
                <p class="form-help">PDF of Word-document tot 10 MB.</p>
                @if ($user->terms_original_name)
                    <p class="profile-file-current">Huidig bestand: {{ $user->terms_original_name }}</p>
                @endif
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
            <button type="submit" class="btn btn-primary">Documenten opslaan</button>
        </div>
    </form>
@endsection
