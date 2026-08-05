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
    @php
        $selectedContactId = old('contact_id');
        $selectedContact = $selectedContactId ? $contacts->firstWhere('id', (int) $selectedContactId) : null;
    @endphp

    <div class="dashboard-create-layout">
        <div class="dashboard-create-form-panel">
            <form method="POST" action="{{ route('dashboard.create.store') }}" class="dashboard-form" enctype="multipart/form-data">
                @csrf

                <section class="dashboard-panel dashboard-panel-wide">
                    <h2>Opdrachtgever selecteren</h2>

                    <div class="contact-search" data-contact-search>
                        <label for="contact_search">Opdrachtgever</label>
                        <input
                            id="contact_search"
                            class="contact-search-input"
                            type="search"
                            value="{{ $selectedContact?->company_name }}"
                            placeholder="Zoek opdrachtgever"
                            autocomplete="off"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            aria-controls="contact_search_results"
                            data-contact-search-input
                            required
                        >
                        <input id="contact_id" name="contact_id" type="hidden" value="{{ $selectedContact?->id }}" data-contact-search-value>

                        <div id="contact_search_results" class="contact-search-results" role="listbox" data-contact-search-results hidden>
                            @foreach ($contacts as $contact)
                                <button
                                    id="contact_search_option_{{ $contact->id }}"
                                    type="button"
                                    class="contact-search-option"
                                    role="option"
                                    data-contact-search-option
                                    data-contact-id="{{ $contact->id }}"
                                    data-contact-label="{{ $contact->company_name }}"
                                    data-contact-search-text="{{ collect([$contact->company_name, $contact->contactName(), $contact->contact_email, $contact->city])->filter()->implode(' ') }}"
                                >
                                    <strong>{{ $contact->company_name }}</strong>
                                    <span>{{ collect([$contact->contactName(), $contact->contact_email, $contact->city])->filter()->implode(' - ') }}</span>
                                </button>
                            @endforeach
                            <div class="contact-search-empty" data-contact-search-empty hidden>Geen opdrachtgever gevonden</div>
                        </div>
                    </div>
                </section>

                <section class="dashboard-panel dashboard-panel-wide">
                    <h2>Gegevens invullen</h2>

                    <div>
                        <label for="title">Titel</label>
                        <input id="title" name="title" type="text" value="{{ old('title') }}" required>
                    </div>

                    <div class="quill-field" data-quill-field>
                        <label for="description_editor">Opdrachtbeschrijving invullen</label>
                        <div id="description_editor" class="quill-editor" data-quill-editor data-quill-required="true" data-quill-placeholder="Beschrijf de opdracht...">{!! \App\Models\Confirmation::sanitizeDescription(old('description')) ?? '' !!}</div>
                        <textarea id="description" name="description" class="quill-editor-input" data-quill-input>{{ old('description') }}</textarea>
                    </div>

                    @include('partials.dashboard.confirmation-specifications-form')

                    <div class="grid-2">
                        <div>
                            <label for="agreement_date">Startdatum</label>
                            <input id="agreement_date" name="agreement_date" type="date" value="{{ old('agreement_date') }}">
                        </div>
                        <div>
                            <label for="duration">Duur van de opdracht</label>
                            <input id="duration" name="duration" type="text" value="{{ old('duration') }}" placeholder="Bv. 3 maanden of t/m 31-12-2026">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="total_value">Overeengekomen vergoeding</label>
                            <input id="total_value" name="total_value" type="number" step="0.01" min="0" value="{{ old('total_value') }}" placeholder="0,00">
                        </div>
                        <div>
                            <label for="value_vat_type">BTW</label>
                            <select id="value_vat_type" name="value_vat_type">
                                <option value="excl" @selected(old('value_vat_type', 'excl') === 'excl')>Excl. BTW</option>
                                <option value="incl" @selected(old('value_vat_type') === 'incl')>Incl. BTW</option>
                            </select>
                        </div>
                    </div>

                    <div class="quill-field" data-quill-field>
                        <label for="termination_terms_editor">Beëindiging van de opdracht</label>
                        <div id="termination_terms_editor" class="quill-editor" data-quill-editor data-quill-placeholder="Bv. Beide partijen kunnen de opdracht schriftelijk opzeggen met een opzegtermijn van 1 maand.">{!! \App\Models\Confirmation::sanitizeDescription(old('termination_terms')) ?? '' !!}</div>
                        <textarea id="termination_terms" name="termination_terms" class="quill-editor-input" data-quill-input>{{ old('termination_terms') }}</textarea>
                    </div>

                </section>

                <section class="dashboard-panel dashboard-panel-wide">
                    <h2>Bijlage</h2>

                    <div>
                        <label for="attachment">Bestand</label>
                        <input id="attachment" name="attachment" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                        <p class="form-help">Optioneel. PDF, Word, Excel of afbeelding tot 10 MB.</p>
                    </div>
                </section>

                <section class="dashboard-panel dashboard-panel-wide">
                    <h2>Offerte</h2>

                    <div>
                        <label for="quote">Bestand</label>
                        <input id="quote" name="quote" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                        <p class="form-help">Optioneel. PDF, Word, Excel of afbeelding tot 10 MB.</p>
                    </div>
                </section>

                <div class="actions actions-end">
                    <button type="submit" name="submit_action" value="test" class="btn btn-secondary">Verzend test</button>
                    <button type="submit" name="submit_action" value="send" class="btn btn-primary">Verzenden</button>
                </div>
            </form>
        </div>

        @include('partials.dashboard.panel', [
            'title' => 'Vaste gegevens',
            'class' => 'dashboard-create-side-panel',
            'slot' => '
                <p>Algemene voorwaarden, bedrijfsgegevens, basis afspraken, logo en huisstijl beheer je in Mijn profiel.</p>
                <p>Die vaste gegevens worden automatisch toegevoegd aan nieuwe opdrachtbevestigingen en meegenomen in de PDF die bij het verzenden wordt gemaakt.</p>
                <p><a href="'.e(route('dashboard.profile.documents')).'" class="btn btn-secondary">Naar documenten</a></p>
            ',
        ])
    </div>
    @endif
@endsection
