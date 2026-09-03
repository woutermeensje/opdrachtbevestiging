@extends('layouts.admin', [
    'title' => 'Dashboard',
])

@section('content')
    <header class="admin-page-header">
        <p class="admin-eyebrow">Admin</p>
        <h1>Dashboard</h1>
        <p class="admin-page-text">Beheeromgeving. Meer functionaliteit volgt hier later.</p>
    </header>

    <div class="admin-metrics">
        <div class="admin-metric-card">
            <span class="admin-metric-label">Gebruikers</span>
            <strong>{{ $metrics['users'] }}</strong>
        </div>
        <div class="admin-metric-card">
            <span class="admin-metric-label">Admins</span>
            <strong>{{ $metrics['admins'] }}</strong>
        </div>
        <div class="admin-metric-card">
            <span class="admin-metric-label">Opdrachtbevestigingen</span>
            <strong>{{ $metrics['confirmations'] }}</strong>
        </div>
        <div class="admin-metric-card">
            <span class="admin-metric-label">Offertes</span>
            <strong>{{ $metrics['quotes'] }}</strong>
        </div>
    </div>
@endsection
