@extends('layouts.dashboard', [
    'title' => 'Aanmaken',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Aanmaken',
        'title' => 'Nieuwe opdrachtbevestiging',
        'text' => 'Kies een opgeslagen opdrachtgever uit je contacten en vul daarna de inhoud van de opdrachtbevestiging aan.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    @if ($contacts->isEmpty())
        @include('partials.dashboard.panel', [
            'title' => 'Voeg eerst een contact toe',
            'slot' => '
                <p>Je hebt nog geen opdrachtgever in je account staan. Voeg eerst onder Contacten een bedrijf en contactpersoon toe via de KVK API.</p>
                <p><a href="'.e(route('dashboard.contacts')).'" class="btn btn-primary">Naar contacten</a></p>
            ',
        ])
    @else

    @include('partials.dashboard.panel', [
        'title' => 'Gegevens invullen',
        'slot' => '
            <form method="POST" action="'.e(route('dashboard.create.store')).'" class="dashboard-form">
                '.csrf_field().'

                <div class="grid-2">
                    <div>
                        <label for="title">Titel</label>
                        <input id="title" name="title" type="text" value="'.e(old('title')).'" required>
                    </div>
                    <div>
                        <label for="contact_id">Opdrachtgever</label>
                        <select id="contact_id" name="contact_id" required>
                            <option value="">Kies een bedrijf</option>
                            '.collect($contacts)->map(fn ($contact) => '<option value="'.e((string) $contact->id).'" '.(old('contact_id') == $contact->id ? 'selected' : '').'>'.e($contact->company_name.' - '.$contact->contactName().' ('.$contact->contact_email.')').'</option>')->implode('').'
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label for="total_value">Waarde</label>
                        <input id="total_value" name="total_value" type="number" step="0.01" min="0" value="'.e(old('total_value')).'" required>
                    </div>
                    <div>
                        <label for="agreement_date">Opdrachtdatum</label>
                        <input id="agreement_date" name="agreement_date" type="date" value="'.e(old('agreement_date')).'">
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="concept" '.(old('status') === 'concept' || old('status') === null ? 'selected' : '').'>Concept</option>
                            <option value="wacht-op-akkoord" '.(old('status') === 'wacht-op-akkoord' ? 'selected' : '').'>Wacht op akkoord</option>
                            <option value="verzonden" '.(old('status') === 'verzonden' ? 'selected' : '').'>Verzonden</option>
                            <option value="getekend" '.(old('status') === 'getekend' ? 'selected' : '').'>Getekend</option>
                        </select>
                    </div>
                    <div>
                        <label for="expires_at">Vervaldatum</label>
                        <input id="expires_at" name="expires_at" type="date" value="'.e(old('expires_at')).'">
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label for="sent_at">Verzenddatum</label>
                        <input id="sent_at" name="sent_at" type="date" value="'.e(old('sent_at')).'">
                    </div>
                    <div>
                        <label for="signed_at">Tekendatum</label>
                        <input id="signed_at" name="signed_at" type="date" value="'.e(old('signed_at')).'">
                    </div>
                </div>

                <label for="description">Omschrijving</label>
                <textarea id="description" name="description" rows="5">'.e(old('description')).'</textarea>

                <div class="actions actions-end">
                    <button type="submit" class="btn btn-primary">Opdrachtbevestiging opslaan</button>
                </div>
            </form>
        ',
    ])
    @endif
@endsection
