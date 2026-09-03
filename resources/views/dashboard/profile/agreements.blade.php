@extends('layouts.dashboard', [
    'title' => 'Basis afspraken',
])

@php
    $user = auth()->user();
    $defaultAgreements = \App\Models\Confirmation::sanitizeDescription(old('default_agreements', $user->default_agreements)) ?? '';
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Profiel',
        'title' => 'Basis afspraken',
        'text' => 'Beheer vaste afspraken en standaard specificaties voor nieuwe opdrachtbevestigingen.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('dashboard.profile.agreements.update') }}" class="dashboard-form dashboard-profile-form">
        @csrf

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Basis afspraken</h2>

            <div class="quill-field-block">
                <label for="default_agreements_editor">Basis afspraken</label>
                <div class="quill-field" data-quill-field>
                    <div id="default_agreements_editor" class="quill-editor" data-quill-editor data-quill-placeholder="Bijvoorbeeld betalingstermijnen of annuleringsvoorwaarden...">{!! $defaultAgreements !!}</div>
                    <textarea id="default_agreements" name="default_agreements" class="quill-editor-input" data-quill-input>{{ old('default_agreements', $user->default_agreements) }}</textarea>
                </div>
            </div>
        </section>

        <section class="dashboard-panel dashboard-panel-wide">
            <h2>Standaard specificaties</h2>

            @include('partials.dashboard.confirmation-specifications-form', [
                'name' => 'default_specifications',
                'idPrefix' => 'default_specifications',
                'values' => $user->default_specifications ?? [],
            ])
        </section>

        <div class="actions actions-end">
            <button type="submit" class="btn btn-primary">Basis afspraken opslaan</button>
        </div>
    </form>
@endsection
