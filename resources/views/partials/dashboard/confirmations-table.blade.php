<div class="dashboard-table-wrap">
    <table class="dashboard-table dashboard-confirmations-table">
        <thead>
            <tr>
                <th>Referentie</th>
                <th>Opdrachtgever</th>
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
                        <strong>{{ $confirmation->client_name }}</strong>
                    </td>
                    <td><span class="dashboard-status dashboard-status-{{ $confirmation->status }}">{{ ucfirst($confirmation->status) }}</span></td>
                    <td>{{ optional($confirmation->sent_at)->format('d-m-Y') ?? '-' }}</td>
                    <td>
                        @if ($confirmation->hasPdf())
                            <a href="{{ route('dashboard.confirmations.pdf', $confirmation) }}">Download</a>
                        @else
                            <span class="dashboard-table-subtle">-</span>
                        @endif
                    </td>
                    <td><a href="{{ route('dashboard.confirmations.show', $confirmation) }}">Bekijken</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
