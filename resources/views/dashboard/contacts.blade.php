@extends('layouts.dashboard', [
    'title' => 'Opdrachtgevers',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Opdrachtgevers',
        'title' => 'Opdrachtgevers en contactpersonen',
        'text' => 'Overzicht van de opdrachtgevers die je hebt toegevoegd.',
    ])

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <div class="actions actions-end dashboard-list-actions">
        <a href="{{ route('dashboard.contacts.create') }}" class="btn btn-primary">Opdrachtgever toevoegen</a>
    </div>

    @include('partials.dashboard.panel', [
        'title' => 'Opdrachtgevers',
        'class' => 'dashboard-panel-wide',
        'slot' => $contacts->isEmpty()
            ? '<p>Er zijn nog geen opdrachtgevers toegevoegd.</p>'
            : view('partials.dashboard.contacts-table', ['contacts' => $contacts])->render(),
    ])
@endsection
