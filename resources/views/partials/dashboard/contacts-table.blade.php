<div class="dashboard-table-wrap">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>Bedrijf</th>
                <th>Contactpersoon</th>
                <th>Adres</th>
                <th>KVK</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contacts as $contact)
                <tr>
                    <td>
                        <strong>{{ $contact->company_name }}</strong>
                        <div class="dashboard-table-subtle">{{ $contact->contact_email }}</div>
                    </td>
                    <td>{{ $contact->contactName() }}</td>
                    <td>
                        {{ trim(collect([$contact->street_name, $contact->house_number, $contact->house_number_addition])->filter()->implode(' ')) ?: '-' }}
                        <div class="dashboard-table-subtle">
                            {{ trim(collect([$contact->postal_code, $contact->city, $contact->country])->filter()->implode(' ')) ?: '-' }}
                        </div>
                    </td>
                    <td>{{ $contact->kvk_number }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
