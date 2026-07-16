<div class="dashboard-table-wrap">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Referentie</th>
                <th>Opdrachtgever</th>
                <th>Status</th>
                <th>Geldig tot</th>
                <th>Verzenddatum</th>
                <th>PDF</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotes as $quote)
                <tr>
                    <td>{{ $quote->reference }}</td>
                    <td>
                        <strong>{{ $quote->client_name }}</strong>
                        <div class="dashboard-table-subtle">{{ $quote->client_contact_name ?: $quote->client_email }}</div>
                    </td>
                    <td><span class="dashboard-status dashboard-status-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span></td>
                    <td>{{ optional($quote->valid_until)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ optional($quote->sent_at)->format('d-m-Y') ?? '-' }}</td>
                    <td>
                        @if ($quote->hasPdf())
                            <a href="{{ route('dashboard.quotes.pdf', $quote) }}">Download</a>
                        @else
                            <span class="dashboard-table-subtle">-</span>
                        @endif
                    </td>
                    <td><a href="{{ route('dashboard.quotes.show', $quote) }}">Bekijken</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
