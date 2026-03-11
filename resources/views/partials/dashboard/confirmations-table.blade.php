<div class="dashboard-table-wrap">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Referentie</th>
                <th>Klant</th>
                <th>Waarde</th>
                <th>Status</th>
                <th>Opdrachtdatum</th>
                <th>Tekendatum</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($confirmations as $confirmation)
                <tr>
                    <td>{{ $confirmation->reference }}</td>
                    <td>
                        <strong>{{ $confirmation->client_name }}</strong>
                        <div class="dashboard-table-subtle">{{ $confirmation->client_contact_name ?: $confirmation->client_email }}</div>
                    </td>
                    <td>EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }}</td>
                    <td><span class="dashboard-status dashboard-status-{{ $confirmation->status }}">{{ ucfirst($confirmation->status) }}</span></td>
                    <td>{{ optional($confirmation->agreement_date)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ optional($confirmation->signed_at)->format('d-m-Y') ?? '-' }}</td>
                    <td><a href="{{ route('dashboard.confirmations.show', $confirmation) }}">Bekijken</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
