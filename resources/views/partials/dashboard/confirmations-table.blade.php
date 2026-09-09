<div class="dashboard-table-wrap">
    <table class="dashboard-table dashboard-confirmations-table">
        <thead>
            <tr>
                <th>Referentie</th>
                <th>Relatie</th>
                <th>Status</th>
                <th>Verzenddatum</th>
                <th>PDF</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($confirmations as $confirmation)
                <tr>
                    <td>{{ $confirmation->reference }}</td>
                    <td>
                        <strong>{{ $confirmation->client_name ?: 'Nog geen relatie' }}</strong>
                        <div class="dashboard-table-subtle">{{ $confirmation->clientRoleLabel() }}</div>
                    </td>
                    <td>
                        <span class="dashboard-status dashboard-status-{{ $confirmation->status }}">
                            {{ $confirmation->is_draft ? 'Concept (niet verzonden)' : ucfirst($confirmation->status) }}
                        </span>
                    </td>
                    <td>{{ optional($confirmation->sent_at)->format('d-m-Y') ?? '-' }}</td>
                    <td>
                        @if ($confirmation->hasPdf())
                            <a href="{{ route('dashboard.confirmations.pdf', $confirmation) }}">Download</a>
                        @else
                            <span class="dashboard-table-subtle">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($confirmation->is_draft)
                            <a href="{{ route('dashboard.create') }}">Verder invullen</a>
                        @else
                            <a href="{{ route('dashboard.confirmations.show', $confirmation) }}">Bekijken</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
