@extends('layouts.admin', [
    'title' => 'Dashboard',
])

@section('content')
    <header class="admin-page-header">
        <p class="admin-eyebrow">Admin</p>
        <h1>Dashboard</h1>
        <p class="admin-page-text">Beheeromgeving met registraties, accountstatussen en globale activiteit.</p>
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

    <section class="admin-section" aria-labelledby="admin-users-title">
        <div class="admin-section-header">
            <div>
                <p class="admin-eyebrow">Accounts</p>
                <h2 id="admin-users-title">Aangemelde gebruikers</h2>
            </div>
            <span class="admin-section-count">{{ $users->count() }} totaal</span>
        </div>

        @if ($users->isEmpty())
            <div class="admin-empty-state">
                <p>Nog geen gebruikers aangemeld.</p>
            </div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-users-table">
                    <thead>
                        <tr>
                            <th scope="col">Gebruiker</th>
                            <th scope="col">Bedrijf</th>
                            <th scope="col">Rol</th>
                            <th scope="col">Accountstatus</th>
                            <th scope="col">Abonnement</th>
                            <th scope="col">Aangemeld op</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    <span>{{ $user->email }}</span>
                                </td>
                                <td>{{ $user->company_name ?: 'Nog niet ingevuld' }}</td>
                                <td>
                                    <span class="admin-role-badge {{ $user->isAdmin() ? 'is-admin' : '' }}">
                                        {{ $user->isAdmin() ? 'Admin' : 'Gebruiker' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-status-badge is-{{ $user->accountStatusTone() }}">
                                        {{ $user->accountStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $user->subscriptionPlanName() ?? 'Geen plan' }}</strong>
                                    <span>
                                        @if ($user->subscription_renews_at)
                                            Verlenging {{ $user->subscription_renews_at->format('d-m-Y') }}
                                        @elseif ($user->trial_ends_at && $user->isOnTrial())
                                            Trial tot {{ $user->trial_ends_at->format('d-m-Y') }}
                                        @else
                                            {{ ucfirst($user->subscription_status ?? 'onbekend') }}
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $user->created_at?->format('d-m-Y H:i') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
