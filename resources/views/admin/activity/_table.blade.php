<div class="admin-table-wrap">
    <table class="admin-users-table admin-activity-table">
        <thead>
            <tr>
                <th scope="col">Tijdstip</th>
                <th scope="col">Gebruiker</th>
                <th scope="col">Activiteit</th>
                <th scope="col">Betreft</th>
                <th scope="col">IP-adres</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($activities as $activity)
                <tr>
                    <td>
                        <strong>{{ $activity->created_at->format('d-m-Y') }}</strong>
                        <span>{{ $activity->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        @if ($activity->user_id)
                            <a href="{{ route('admin.activity', ['user' => $activity->user_id]) }}" class="admin-activity-user">
                                <strong>{{ $activity->user_name }}</strong>
                            </a>
                        @else
                            <strong>{{ $activity->user_name ?? 'Onbekend' }}</strong>
                        @endif
                        <span>{{ $activity->user_email }}</span>
                    </td>
                    <td>
                        <span class="admin-status-badge is-{{ $activity->tone() }}">{{ $activity->description }}</span>
                    </td>
                    <td>{{ $activity->subjectLabel() ?? '-' }}</td>
                    <td>
                        <span>{{ $activity->ip_address ?? '-' }}</span>
                        @if ($activity->user_agent)
                            <span class="admin-activity-agent" title="{{ $activity->user_agent }}">{{ str($activity->user_agent)->limit(40) }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
