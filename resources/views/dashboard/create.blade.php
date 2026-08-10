@extends('layouts.dashboard', [
    'title' => 'Aanmaken',
])

@php
    $user = auth()->user();
    $selectedContactId = old('contact_id');
    $selectedContact = $selectedContactId ? $contacts->firstWhere('id', (int) $selectedContactId) : null;
    $senderName = trim((string) $user->first_name.' '.(string) $user->last_name);
    $senderAddressLines = $user->companyAddressLines();
    $logoDataUri = null;

    if (filled($user->company_logo_path) && \Illuminate\Support\Facades\Storage::disk('local')->exists($user->company_logo_path)) {
        $logoDataUri = 'data:'.($user->company_logo_mime_type ?: 'image/png').';base64,'.base64_encode(
            \Illuminate\Support\Facades\Storage::disk('local')->get($user->company_logo_path),
        );
    }

    $descriptionHtml = \App\Models\Confirmation::sanitizeDescription(old('description'));
    $defaultAgreementsHtml = $user->defaultAgreementsHtml();
    $specificationValues = old('specifications', $user->default_specifications ?? []);
    $clientReference = data_get($specificationValues, 'general.client_reference');
    $projectName = data_get($specificationValues, 'general.project_name');
    $workLocation = data_get($specificationValues, 'general.work_location');
    $startDate = data_get($specificationValues, 'planning.start_date') ?: old('agreement_date');
    $endDate = data_get($specificationValues, 'planning.end_date');
    $rate = data_get($specificationValues, 'financial.rate');
    $rateUnit = data_get($specificationValues, 'financial.rate_unit');
    $vatPercentage = data_get($specificationValues, 'financial.vat_percentage');
    $totalValue = old('total_value') ?: data_get($specificationValues, 'financial.total_amount');
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Aanmaken',
        'title' => 'Nieuwe opdrachtbevestiging',
        'text' => 'Begin met de visuele opdrachtbevestiging en vul daarna gericht de opdrachtgever en inhoud aan.',
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
        <form
            id="confirmation_create_form"
            method="POST"
            action="{{ route('dashboard.create.store') }}"
            class="dashboard-form confirmation-builder"
            enctype="multipart/form-data"
            data-confirmation-builder
            data-initial-panel="{{ $errors->has('contact_id') ? 'client' : ($errors->any() ? 'confirmation' : '') }}"
        >
            @csrf

            <div class="confirmation-builder-shell">
                <div class="confirmation-builder-stage">
                    <section class="confirmation-edit-panel" data-builder-panel="client" hidden>
                        <div class="confirmation-edit-panel-header">
                            <div>
                                <p class="confirmation-edit-panel-eyebrow">Stap 1</p>
                                <h2>Opdrachtgever selecteren</h2>
                            </div>
                            <button type="button" class="btn btn-secondary btn-small" data-builder-close>Sluiten</button>
                        </div>

                        <div class="confirmation-edit-panel-grid">
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
                                >
                                <input id="contact_id" name="contact_id" type="hidden" value="{{ $selectedContact?->id }}" data-contact-search-value>

                                <div id="contact_search_results" class="contact-search-results" role="listbox" data-contact-search-results hidden>
                                    @foreach ($contacts as $contact)
                                        @php
                                            $contactStreetLine = trim(implode(' ', array_filter([
                                                $contact->street_name,
                                                $contact->house_number,
                                                $contact->house_number_addition,
                                            ])));
                                            $contactCityLine = trim(implode(' ', array_filter([
                                                $contact->postal_code,
                                                $contact->city,
                                            ])));
                                            $contactAddressLines = collect([$contactStreetLine, $contactCityLine, $contact->country])->filter()->values();
                                        @endphp

                                        <button
                                            id="contact_search_option_{{ $contact->id }}"
                                            type="button"
                                            class="contact-search-option"
                                            role="option"
                                            data-contact-search-option
                                            data-contact-id="{{ $contact->id }}"
                                            data-contact-label="{{ $contact->company_name }}"
                                            data-contact-search-text="{{ collect([$contact->company_name, $contact->contactName(), $contact->contact_email, $contact->city])->filter()->implode(' ') }}"
                                            data-contact-company="{{ $contact->company_name }}"
                                            data-contact-person="{{ $contact->contactName() }}"
                                            data-contact-email="{{ $contact->contact_email }}"
                                            data-contact-kvk="{{ $contact->kvk_number }}"
                                            data-contact-address="{{ $contactAddressLines->implode('||') }}"
                                        >
                                            <strong>{{ $contact->company_name }}</strong>
                                            <span>{{ collect([$contact->contactName(), $contact->contact_email, $contact->city])->filter()->implode(' - ') }}</span>
                                        </button>
                                    @endforeach
                                    <div class="contact-search-empty" data-contact-search-empty hidden>Geen opdrachtgever gevonden</div>
                                </div>
                            </div>

                            <div>
                                <label for="selected_contact_person">Contactpersoon</label>
                                <input
                                    id="selected_contact_person"
                                    type="text"
                                    value="{{ $selectedContact?->contactName() }}"
                                    placeholder="Wordt gevuld na selectie"
                                    data-selected-contact-person
                                    readonly
                                >
                            </div>

                            <div>
                                <label for="selected_contact_email">E-mailadres</label>
                                <input
                                    id="selected_contact_email"
                                    type="email"
                                    value="{{ $selectedContact?->contact_email }}"
                                    placeholder="Wordt gevuld na selectie"
                                    data-selected-contact-email
                                    readonly
                                >
                            </div>

                            <div>
                                <label for="client_reference_quick">Kenmerk opdrachtgever</label>
                                <input
                                    id="client_reference_quick"
                                    type="text"
                                    value="{{ $clientReference }}"
                                    placeholder="Bijvoorbeeld PO-nummer of intern kenmerk"
                                    data-preview-input="client-reference"
                                    data-sync-input="#specifications_general_client_reference"
                                >
                            </div>
                        </div>

                        <div class="confirmation-edit-panel-actions">
                            <button type="button" class="btn btn-primary" data-builder-open="confirmation">Opdrachtbevestiging invullen</button>
                        </div>
                    </section>

                    <section class="confirmation-edit-panel confirmation-edit-panel-wide" data-builder-panel="confirmation" hidden>
                        <div class="confirmation-edit-panel-header">
                            <div>
                                <p class="confirmation-edit-panel-eyebrow">Stap 2</p>
                                <h2>Opdrachtbevestiging invullen</h2>
                            </div>
                            <button type="button" class="btn btn-secondary btn-small" data-builder-close>Sluiten</button>
                        </div>

                        <div class="confirmation-edit-panel-grid">
                            <div class="confirmation-edit-panel-field-wide">
                                <label for="title">Titel</label>
                                <input
                                    id="title"
                                    name="title"
                                    type="text"
                                    value="{{ old('title') }}"
                                    placeholder="Bijvoorbeeld: Opdrachtbevestiging interim recruitment"
                                    data-preview-input="title"
                                >
                            </div>

                            <div>
                                <label for="agreement_date">Startdatum</label>
                                <input id="agreement_date" name="agreement_date" type="date" value="{{ old('agreement_date') }}" data-preview-input="agreement-date" data-sync-input="#specifications_planning_start_date">
                            </div>

                            <div>
                                <label for="duration">Duur van de opdracht</label>
                                <input id="duration" name="duration" type="text" value="{{ old('duration') }}" placeholder="Bijvoorbeeld 6 maanden" data-preview-input="duration" data-sync-input="#specifications_planning_expected_duration">
                            </div>

                            <div>
                                <label for="total_value">Vergoeding</label>
                                <input id="total_value" name="total_value" type="number" step="0.01" min="0" value="{{ old('total_value') }}" placeholder="0,00" data-preview-input="total-value" data-sync-input="#specifications_financial_total_amount">
                            </div>

                            <div>
                                <label for="value_vat_type">BTW</label>
                                <select id="value_vat_type" name="value_vat_type" data-preview-input="vat-type">
                                    <option value="excl" @selected(old('value_vat_type', 'excl') === 'excl')>Excl. BTW</option>
                                    <option value="incl" @selected(old('value_vat_type') === 'incl')>Incl. BTW</option>
                                </select>
                            </div>
                        </div>

                        <div class="quill-field" data-quill-field data-ai-assist="true" data-ai-assist-context="opdrachtbeschrijving" data-ai-assist-url="{{ route('dashboard.ai-assist.text') }}">
                            <label for="description_editor">Omschrijving van de opdracht</label>
                            <div id="description_editor" class="quill-editor" data-quill-editor data-quill-required="true" data-quill-placeholder="Beschrijf de opdracht...">{!! $descriptionHtml ?? '' !!}</div>
                            <textarea id="description" name="description" class="quill-editor-input" data-quill-input data-preview-input="description">{{ old('description') }}</textarea>
                        </div>

                        <details class="confirmation-panel-details" open>
                            <summary>Specificaties en afspraken</summary>
                            @include('partials.dashboard.confirmation-specifications-form', [
                                'values' => $user->default_specifications ?? [],
                            ])
                        </details>

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

                        <section
                            class="confirmation-ai-inline"
                            data-ai-toolkit
                            data-ai-form-selector="#confirmation_create_form"
                            data-ai-generate-url="{{ route('dashboard.ai-assist.concept') }}"
                            data-ai-check-url="{{ route('dashboard.ai-assist.check') }}"
                        >
                            <h3>AI-assistent</h3>

                            <label for="ai_brief">Korte opdracht</label>
                            <textarea
                                id="ai_brief"
                                data-ai-brief
                                rows="3"
                                placeholder="Bijvoorbeeld: website bouwen voor Acme, 3 pagina's, EUR 1.500 excl. btw, oplevering eind september."
                            ></textarea>

                            <div class="dashboard-panel-actions">
                                <button type="button" class="btn btn-secondary btn-small" data-ai-generate>Concept maken</button>
                                <button type="button" class="btn btn-secondary btn-small" data-ai-check>Compleetheid checken</button>
                            </div>

                            <p class="form-help ai-status" data-ai-status></p>
                            <div class="ai-check-results" data-ai-check-results hidden></div>
                        </section>

                        <div class="confirmation-edit-panel-actions">
                            <button type="button" class="btn btn-secondary" data-builder-close>Klaar met invullen</button>
                            <button type="submit" name="submit_action" value="send" class="btn btn-primary">Verzenden</button>
                        </div>
                    </section>

                    <article class="confirmation-document-preview" style="--preview-accent: {{ $user->primary_color ?: '#003B73' }};">
                        <header class="confirmation-document-header">
                            <div class="confirmation-document-logo">
                                @if ($logoDataUri !== null)
                                    <img src="{{ $logoDataUri }}" alt="{{ $user->company_name }} logo">
                                @else
                                    <span>{{ $user->company_name ?: config('app.name') }}</span>
                                @endif
                            </div>

                            <div class="confirmation-document-sender">
                                <strong>{{ $user->company_name ?: 'Jouw bedrijfsnaam' }}</strong>
                                @forelse ($senderAddressLines as $line)
                                    <span>{{ $line }}</span>
                                @empty
                                    <span>Bedrijfsadres nog niet ingevuld</span>
                                @endforelse
                                <span>{{ $user->email }}</span>
                                @if (filled($user->kvk_number))
                                    <span>KVK: {{ $user->kvk_number }}</span>
                                @endif
                            </div>
                        </header>

                        <section
                            class="confirmation-document-recipient confirmation-document-click-target"
                            role="button"
                            tabindex="0"
                            data-builder-open="client"
                            aria-expanded="false"
                            aria-label="Opdrachtgever selecteren"
                        >
                            <p class="confirmation-document-label">Opdrachtgever</p>
                            <strong data-preview-target="client-company">{{ $selectedContact?->company_name ?: 'Nog te selecteren' }}</strong>
                            <span data-preview-target="client-person">{{ $selectedContact?->contactName() ?: 'Contactpersoon' }}</span>
                            <span data-preview-target="client-email">{{ $selectedContact?->contact_email ?: 'E-mailadres opdrachtgever' }}</span>
                            <span data-preview-target="client-address">
                                @if ($selectedContact)
                                    @php
                                        $selectedStreetLine = trim(implode(' ', array_filter([
                                            $selectedContact->street_name,
                                            $selectedContact->house_number,
                                            $selectedContact->house_number_addition,
                                        ])));
                                        $selectedCityLine = trim(implode(' ', array_filter([
                                            $selectedContact->postal_code,
                                            $selectedContact->city,
                                        ])));
                                    @endphp
                                    {{ collect([$selectedStreetLine, $selectedCityLine, $selectedContact->country])->filter()->implode(' · ') }}
                                @else
                                    Adres opdrachtgever
                                @endif
                            </span>
                        </section>

                        <div
                            class="confirmation-document-click-target confirmation-document-content-target"
                            role="button"
                            tabindex="0"
                            data-builder-open="confirmation"
                            aria-expanded="false"
                            aria-label="Opdrachtbevestiging invullen"
                        >
                            <section class="confirmation-document-title-row">
                                <div>
                                    <p class="confirmation-document-label">Concept</p>
                                    <h2>
                                        <span data-preview-target="title">{{ old('title') ?: 'Opdrachtbevestiging' }}</span>
                                        <span>Concept</span>
                                    </h2>
                                </div>

                                <dl>
                                    <div>
                                        <dt>Aanmaakdatum</dt>
                                        <dd>{{ now()->format('d-m-Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt>Startdatum</dt>
                                        <dd data-preview-target="agreement-date">{{ $startDate ?: 'Nog niet ingevuld' }}</dd>
                                    </div>
                                </dl>
                            </section>

                            <section class="confirmation-document-section">
                                <div class="confirmation-document-section-heading">
                                    <p class="confirmation-document-label">Omschrijving</p>
                                    <span>1. Opdracht beschrijven</span>
                                </div>
                                <div class="confirmation-document-rich" data-preview-target="description">
                                    @if ($descriptionHtml !== null)
                                        {!! $descriptionHtml !!}
                                    @else
                                        <p>Beschrijf hier de opdracht, werkzaamheden, verwachtingen en oplevering.</p>
                                    @endif
                                </div>
                            </section>

                            <section class="confirmation-document-section">
                                <div class="confirmation-document-section-heading">
                                    <p class="confirmation-document-label">Afspraken</p>
                                    <span>2. Velden aanvullen</span>
                                </div>

                                <div class="confirmation-document-summary-grid">
                                    <div>
                                        <span>Kenmerk opdrachtgever</span>
                                        <strong data-preview-target="client-reference">{{ $clientReference ?: 'Nog niet ingevuld' }}</strong>
                                    </div>
                                    <div>
                                        <span>Projectnaam</span>
                                        <strong data-preview-target="project-name">{{ $projectName ?: 'Nog niet ingevuld' }}</strong>
                                    </div>
                                    <div>
                                        <span>Locatie werkzaamheden</span>
                                        <strong data-preview-target="work-location">{{ $workLocation ?: 'Nog niet ingevuld' }}</strong>
                                    </div>
                                    <div>
                                        <span>Duur van de opdracht</span>
                                        <strong data-preview-target="duration">{{ old('duration') ?: 'Nog niet ingevuld' }}</strong>
                                    </div>
                                    <div>
                                        <span>Periode</span>
                                        <strong>
                                            <span data-preview-target="start-date">{{ $startDate ?: 'Startdatum' }}</span>
                                            <span> - </span>
                                            <span data-preview-target="end-date">{{ $endDate ?: 'Einddatum' }}</span>
                                        </strong>
                                    </div>
                                    <div>
                                        <span>Tarief</span>
                                        <strong>
                                            <span data-preview-target="rate">{{ $rate ?: '0,00' }}</span>
                                            <span data-preview-target="rate-unit">{{ $rateUnit ?: '' }}</span>
                                        </strong>
                                    </div>
                                    <div>
                                        <span>Vergoeding</span>
                                        <strong>
                                            <span data-preview-target="total-value">{{ $totalValue ? 'EUR '.number_format((float) $totalValue, 2, ',', '.') : 'EUR 0,00' }}</span>
                                            <span data-preview-target="vat-type">{{ old('value_vat_type', 'excl') === 'incl' ? 'incl. BTW' : 'excl. BTW' }}</span>
                                        </strong>
                                    </div>
                                    <div>
                                        <span>BTW-percentage</span>
                                        <strong data-preview-target="vat-percentage">{{ $vatPercentage ?: '21' }}%</strong>
                                    </div>
                                </div>
                            </section>

                            <section class="confirmation-document-section confirmation-document-terms">
                                <div class="confirmation-document-section-heading">
                                    <p class="confirmation-document-label">Vaste afspraken</p>
                                    <span>3. Automatisch toegevoegd</span>
                                </div>

                                @if ($defaultAgreementsHtml !== '')
                                    <div class="confirmation-document-rich">{!! $defaultAgreementsHtml !!}</div>
                                @else
                                    <p>Je basis afspraken en algemene voorwaarden worden hier meegenomen zodra je die in Mijn profiel hebt ingesteld.</p>
                                @endif
                            </section>
                        </div>

                        <footer class="confirmation-document-footer">
                            <span>Opgesteld door {{ $user->company_name ?: $senderName ?: config('app.name') }}</span>
                            <span data-preview-target="client-reference-footer">{{ $clientReference ? 'Kenmerk '.$clientReference : 'Concept zonder kenmerk' }}</span>
                        </footer>
                    </article>
                </div>

                <aside class="confirmation-builder-aside">
                    <div class="confirmation-builder-status-card">
                        <span class="dashboard-status dashboard-status-concept">Concept</span>
                        <dl>
                            <div>
                                <dt>Opdrachtgever</dt>
                                <dd data-preview-target="aside-client">{{ $selectedContact?->company_name ?: 'Nog niet geselecteerd' }}</dd>
                            </div>
                            <div>
                                <dt>Workflow</dt>
                                <dd>Standaard</dd>
                            </div>
                            <div>
                                <dt>Datum</dt>
                                <dd>{{ now()->translatedFormat('j F Y') }}</dd>
                            </div>
                        </dl>

                        <div class="confirmation-builder-submit-actions">
                            <button type="submit" name="submit_action" value="test" class="btn btn-secondary">Verzend test</button>
                            <button type="submit" name="submit_action" value="send" class="btn btn-primary">Verzenden</button>
                        </div>
                    </div>

                    <div class="confirmation-builder-help-card">
                        <h2>Vaste gegevens</h2>
                        <p>Bedrijfsgegevens, basis afspraken, logo en huisstijl worden automatisch toegevoegd aan de PDF.</p>
                        <a href="{{ route('dashboard.profile.brand') }}" class="btn btn-secondary btn-small">Logo & huisstijl</a>
                        <a href="{{ route('dashboard.profile.agreements') }}" class="btn btn-secondary btn-small">Basis afspraken</a>
                    </div>
                </aside>
            </div>
        </form>
    @endif
@endsection
