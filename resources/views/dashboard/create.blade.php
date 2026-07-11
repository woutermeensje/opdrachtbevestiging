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
                <p>Je hebt nog geen opdrachtgever in je account staan. Voeg eerst een bedrijf en contactpersoon toe.</p>
                <p><a href="'.e(route('dashboard.contacts.create')).'" class="btn btn-primary">Opdrachtgever toevoegen</a></p>
            ',
        ])
    @else

    <div class="dashboard-create-layout">
        @include('partials.dashboard.panel', [
            'title' => 'Gegevens invullen',
            'class' => 'dashboard-create-form-panel',
            'slot' => '
                <form method="POST" action="'.e(route('dashboard.create.store')).'" class="dashboard-form" enctype="multipart/form-data">
                    '.csrf_field().'

                    <div>
                        <label for="title">Titel</label>
                        <input id="title" name="title" type="text" value="'.e(old('title')).'" required>
                    </div>

                    <div class="quill-field" data-quill-field>
                        <label for="description_editor">Tekstblok</label>
                        <div id="description_editor" class="quill-editor" data-quill-editor data-quill-required="true" data-quill-placeholder="Beschrijf de opdracht...">'.(\App\Models\Confirmation::sanitizeDescription(old('description')) ?? '').'</div>
                        <textarea id="description" name="description" class="quill-editor-input" data-quill-input>'.e(old('description')).'</textarea>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="agreement_date">Startdatum</label>
                            <input id="agreement_date" name="agreement_date" type="date" value="'.e(old('agreement_date')).'">
                        </div>
                        <div>
                            <label for="duration">Duur van de opdracht</label>
                            <input id="duration" name="duration" type="text" value="'.e(old('duration')).'" placeholder="Bv. 3 maanden of t/m 31-12-2026">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="total_value">Overeengekomen vergoeding</label>
                            <input id="total_value" name="total_value" type="number" step="0.01" min="0" value="'.e(old('total_value')).'" placeholder="0,00">
                        </div>
                        <div>
                            <label for="value_vat_type">BTW</label>
                            <select id="value_vat_type" name="value_vat_type">
                                <option value="excl"'.(old('value_vat_type', 'excl') === 'excl' ? ' selected' : '').'>Excl. BTW</option>
                                <option value="incl"'.(old('value_vat_type') === 'incl' ? ' selected' : '').'>Incl. BTW</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="termination_terms">Beëindiging van de opdracht</label>
                        <textarea id="termination_terms" name="termination_terms" placeholder="Bv. Beide partijen kunnen de opdracht schriftelijk opzeggen met een opzegtermijn van 1 maand.">'.e(old('termination_terms')).'</textarea>
                    </div>

                    <div>
                        <label for="contact_id">Contact selecteren</label>
                        <select id="contact_id" name="contact_id" required>
                            <option value="">Kies een bedrijf</option>
                            '.collect($contacts)->map(fn ($contact) => '<option value="'.e((string) $contact->id).'" '.(old('contact_id') == $contact->id ? 'selected' : '').'>'.e($contact->company_name).'</option>')->implode('').'
                        </select>
                    </div>

                    <div class="upload-block-grid">
                        <div class="upload-block">
                            <label for="attachment">Bijlage</label>
                            <input id="attachment" name="attachment" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                            <p class="form-help">Optioneel. PDF, Word, Excel of afbeelding tot 10 MB.</p>
                        </div>

                        <div class="upload-block">
                            <label for="quote">Offerte</label>
                            <input id="quote" name="quote" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                            <p class="form-help">Optioneel. PDF, Word, Excel of afbeelding tot 10 MB.</p>
                        </div>
                    </div>

                    <div class="actions actions-end">
                        <button type="submit" name="submit_action" value="test" class="btn btn-secondary">Verzend test</button>
                        <button type="submit" name="submit_action" value="send" class="btn btn-primary">Verzenden</button>
                    </div>
                </form>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Vaste gegevens',
            'class' => 'dashboard-create-side-panel',
            'slot' => '
                <p>Algemene voorwaarden, bedrijfslogo, bedrijfsgegevens en basis afspraken beheer je in Mijn profiel.</p>
                <p>Die vaste gegevens worden automatisch toegevoegd aan nieuwe opdrachtbevestigingen en meegenomen in de PDF die bij het verzenden wordt gemaakt.</p>
                <p><a href="'.e(route('dashboard.profile')).'" class="btn btn-secondary">Naar Mijn profiel</a></p>
            ',
        ])
    </div>
    @endif
@endsection
