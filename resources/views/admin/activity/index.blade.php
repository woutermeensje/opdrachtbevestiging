@extends('layouts.admin', [
    'title' => 'Activiteit',
])

@section('content')
    <header class="admin-page-header">
        <p class="admin-eyebrow">Admin</p>
        <h1>Activiteit</h1>
        <p class="admin-page-text">Logboek van inlogmomenten en acties van gebruikers. Activiteiten worden {{ config('activity.retention_days') }} dagen bewaard.</p>
    </header>

    <form method="GET" action="{{ route('admin.activity') }}" class="admin-filters">
        <label>
            <span>Gebruiker</span>
            <select name="user">
                <option value="">Alle gebruikers</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(($filters['user'] ?? null) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
        </label>
        <label>
            <span>Activiteit</span>
            <select name="action">
                <option value="">Alle activiteiten</option>
                @foreach ($actionOptions as $group => $options)
                    <optgroup label="{{ $group }}">
                        @foreach ($options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['action'] ?? null) === $value)>{{ $label }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </label>
        <label>
            <span>Vanaf</span>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
        </label>
        <label>
            <span>Tot en met</span>
            <input type="date" name="until" value="{{ $filters['until'] ?? '' }}">
        </label>
        <div class="admin-filters-actions">
            <button type="submit" class="admin-filter-button">Filteren</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.activity') }}" class="admin-topbar-link">Wissen</a>
            @endif
        </div>
    </form>

    <section class="admin-section" aria-labelledby="admin-activity-title">
        <div class="admin-section-header">
            <div>
                <p class="admin-eyebrow">Logboek</p>
                <h2 id="admin-activity-title">Activiteiten</h2>
            </div>
            <span class="admin-section-count">{{ $activities->total() }} resultaten</span>
        </div>

        @if ($activities->isEmpty())
            <div class="admin-empty-state">
                <p>Geen activiteiten gevonden.</p>
            </div>
        @else
            @include('admin.activity._table', ['activities' => $activities])

            @if ($activities->hasPages())
                <nav class="admin-pagination" aria-label="Paginering">
                    @if ($activities->onFirstPage())
                        <span class="admin-topbar-link is-disabled">← Vorige</span>
                    @else
                        <a href="{{ $activities->previousPageUrl() }}" class="admin-topbar-link">← Vorige</a>
                    @endif
                    <span>Pagina {{ $activities->currentPage() }} van {{ $activities->lastPage() }}</span>
                    @if ($activities->hasMorePages())
                        <a href="{{ $activities->nextPageUrl() }}" class="admin-topbar-link">Volgende →</a>
                    @else
                        <span class="admin-topbar-link is-disabled">Volgende →</span>
                    @endif
                </nav>
            @endif
        @endif
    </section>
@endsection
