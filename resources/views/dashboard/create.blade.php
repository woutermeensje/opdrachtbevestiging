@extends('layouts.dashboard', [
    'title' => 'Aanmaken',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Aanmaken',
        'title' => 'Nieuwe opdrachtbevestiging',
        'text' => 'Maak hier een nieuwe opdrachtbevestiging aan met klantgegevens, opdrachtwaarde en belangrijke data.',
    ])

    @include('partials.forms.errors')

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
                        <label for="client_name">Klantnaam</label>
                        <input id="client_name" name="client_name" type="text" value="'.e(old('client_name')).'" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div>
                        <label for="client_email">E-mailadres klant</label>
                        <input id="client_email" name="client_email" type="email" value="'.e(old('client_email')).'" required>
                    </div>
                    <div>
                        <label for="total_value">Waarde</label>
                        <input id="total_value" name="total_value" type="number" step="0.01" min="0" value="'.e(old('total_value')).'" required>
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
                        <label for="agreement_date">Opdrachtdatum</label>
                        <input id="agreement_date" name="agreement_date" type="date" value="'.e(old('agreement_date')).'">
                    </div>
                </div>

                <div class="grid-3">
                    <div>
                        <label for="sent_at">Verzenddatum</label>
                        <input id="sent_at" name="sent_at" type="date" value="'.e(old('sent_at')).'">
                    </div>
                    <div>
                        <label for="signed_at">Tekendatum</label>
                        <input id="signed_at" name="signed_at" type="date" value="'.e(old('signed_at')).'">
                    </div>
                    <div>
                        <label for="expires_at">Vervaldatum</label>
                        <input id="expires_at" name="expires_at" type="date" value="'.e(old('expires_at')).'">
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
@endsection
