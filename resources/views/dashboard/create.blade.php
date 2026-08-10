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
    $specificationSections = \App\Models\Confirmation::specificationSections();
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

    $labelClass = 'mb-2 block text-sm font-medium text-gray-900';
    $inputClass = 'mb-0 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-700 focus:ring-blue-700';
    $fileClass = 'mb-0 block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-700 file:me-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-gray-900 hover:file:bg-gray-200';
    $primaryButtonClass = 'inline-flex items-center justify-center rounded-lg bg-blue-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-4 focus:ring-blue-300';
    $secondaryButtonClass = 'inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-800 focus:outline-none focus:ring-4 focus:ring-gray-100';
@endphp

@section('content')
    <section class="mb-6 rounded-lg border border-gray-200 bg-white p-6">
        <p class="mb-2 text-sm font-semibold text-blue-800">Aanmaken</p>
        <h1 class="mb-3 text-3xl font-extrabold tracking-tight text-gray-900 md:text-4xl">Nieuwe opdrachtbevestiging</h1>
        <p class="max-w-3xl text-base text-gray-600">Begin met de visuele opdrachtbevestiging en vul daarna gericht de opdrachtgever en inhoud aan.</p>
    </section>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="mb-2 font-semibold">Controleer de volgende velden:</p>
            <ul class="m-0 list-disc space-y-1 ps-5">
                @foreach ($errors->all() as $error)
                    <li class="m-0 text-sm text-red-800">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($contacts->isEmpty())
        <section class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-2 text-xl font-semibold text-gray-900">Voeg eerst een contact toe</h2>
            <p class="mb-4 text-sm text-gray-600">Je hebt nog geen opdrachtgever in je account staan. Voeg eerst een bedrijf en contactpersoon toe.</p>
            <a href="{{ route('dashboard.contacts.create') }}" class="{{ $primaryButtonClass }}">Opdrachtgever toevoegen</a>
        </section>
    @else
        <form
            id="confirmation_create_form"
            method="POST"
            action="{{ route('dashboard.create.store') }}"
            class="space-y-0"
            enctype="multipart/form-data"
            data-confirmation-builder
            data-flowbite-create
            data-initial-panel="{{ $errors->has('contact_id') ? 'client' : ($errors->any() ? 'confirmation' : '') }}"
        >
            @csrf

            <div class="flex flex-col gap-4 xl:flex-row xl:items-start">
                <div class="relative w-full min-w-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm xl:flex-1" data-template-stage>
                    <section
                        class="fixed inset-x-3 bottom-4 top-20 z-40 overflow-y-auto rounded-lg border border-gray-200 bg-white p-4 shadow-2xl md:absolute md:bottom-auto md:left-4 md:right-4 md:top-4 md:max-h-[calc(100vh-10rem)] md:p-6"
                        data-builder-panel="client"
                        hidden
                    >
                        <div class="mb-5 flex items-start justify-between gap-4 border-b border-gray-200 pb-4">
                            <div>
                                <p class="mb-1 text-sm font-semibold text-blue-800">Stap 1</p>
                                <h2 class="m-0 text-xl font-semibold text-gray-900">Opdrachtgever selecteren</h2>
                            </div>
                            <button type="button" class="{{ $secondaryButtonClass }} px-3 py-2 text-xs" data-builder-close>Sluiten</button>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="relative mb-0" data-contact-search>
                                <label for="contact_search" class="{{ $labelClass }}">Opdrachtgever</label>
                                <input
                                    id="contact_search"
                                    class="{{ $inputClass }}"
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

                                <div
                                    id="contact_search_results"
                                    class="absolute left-0 right-0 z-50 mt-2 max-h-72 overflow-y-auto rounded-lg border border-gray-200 bg-white p-2 shadow-lg"
                                    role="listbox"
                                    data-contact-search-results
                                    hidden
                                >
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
                                            class="w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
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
                                            <strong class="block font-semibold text-gray-900">{{ $contact->company_name }}</strong>
                                            <span class="block text-xs text-gray-500">{{ collect([$contact->contactName(), $contact->contact_email, $contact->city])->filter()->implode(' - ') }}</span>
                                        </button>
                                    @endforeach
                                    <div class="px-3 py-2 text-sm text-gray-500" data-contact-search-empty hidden>Geen opdrachtgever gevonden</div>
                                </div>
                            </div>

                            <div>
                                <label for="selected_contact_person" class="{{ $labelClass }}">Contactpersoon</label>
                                <input
                                    id="selected_contact_person"
                                    class="{{ $inputClass }}"
                                    type="text"
                                    value="{{ $selectedContact?->contactName() }}"
                                    placeholder="Wordt gevuld na selectie"
                                    data-selected-contact-person
                                    readonly
                                >
                            </div>

                            <div>
                                <label for="selected_contact_email" class="{{ $labelClass }}">E-mailadres</label>
                                <input
                                    id="selected_contact_email"
                                    class="{{ $inputClass }}"
                                    type="email"
                                    value="{{ $selectedContact?->contact_email }}"
                                    placeholder="Wordt gevuld na selectie"
                                    data-selected-contact-email
                                    readonly
                                >
                            </div>

                            <div>
                                <label for="client_reference_quick" class="{{ $labelClass }}">Kenmerk opdrachtgever</label>
                                <input
                                    id="client_reference_quick"
                                    class="{{ $inputClass }}"
                                    type="text"
                                    value="{{ $clientReference }}"
                                    placeholder="Bijvoorbeeld PO-nummer of intern kenmerk"
                                    data-preview-input="client-reference"
                                    data-sync-input="#specifications_general_client_reference"
                                >
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end border-t border-gray-200 pt-4">
                            <button type="button" class="{{ $primaryButtonClass }}" data-builder-open="confirmation">Opdrachtbevestiging invullen</button>
                        </div>
                    </section>

                    <section
                        class="fixed inset-x-3 bottom-4 top-20 z-40 overflow-y-auto rounded-lg border border-gray-200 bg-white p-4 shadow-2xl md:absolute md:bottom-auto md:left-4 md:right-4 md:top-4 md:max-h-[calc(100vh-10rem)] md:p-6"
                        data-builder-panel="confirmation"
                        hidden
                    >
                        <div class="mb-5 flex items-start justify-between gap-4 border-b border-gray-200 pb-4">
                            <div>
                                <p class="mb-1 text-sm font-semibold text-blue-800">Stap 2</p>
                                <h2 class="m-0 text-xl font-semibold text-gray-900">Opdrachtbevestiging invullen</h2>
                            </div>
                            <button type="button" class="{{ $secondaryButtonClass }} px-3 py-2 text-xs" data-builder-close>Sluiten</button>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="title" class="{{ $labelClass }}">Titel</label>
                                <input
                                    id="title"
                                    name="title"
                                    class="{{ $inputClass }}"
                                    type="text"
                                    value="{{ old('title') }}"
                                    placeholder="Bijvoorbeeld: Opdrachtbevestiging interim recruitment"
                                    data-preview-input="title"
                                >
                            </div>

                            <div>
                                <label for="agreement_date" class="{{ $labelClass }}">Startdatum</label>
                                <input id="agreement_date" name="agreement_date" class="{{ $inputClass }}" type="date" value="{{ old('agreement_date') }}" data-preview-input="agreement-date" data-sync-input="#specifications_planning_start_date">
                            </div>

                            <div>
                                <label for="duration" class="{{ $labelClass }}">Duur van de opdracht</label>
                                <input id="duration" name="duration" class="{{ $inputClass }}" type="text" value="{{ old('duration') }}" placeholder="Bijvoorbeeld 6 maanden" data-preview-input="duration" data-sync-input="#specifications_planning_expected_duration">
                            </div>

                            <div>
                                <label for="total_value" class="{{ $labelClass }}">Vergoeding</label>
                                <input id="total_value" name="total_value" class="{{ $inputClass }}" type="number" step="0.01" min="0" value="{{ old('total_value') }}" placeholder="0,00" data-preview-input="total-value" data-sync-input="#specifications_financial_total_amount">
                            </div>

                            <div>
                                <label for="value_vat_type" class="{{ $labelClass }}">BTW</label>
                                <select id="value_vat_type" name="value_vat_type" class="{{ $inputClass }}" data-preview-input="vat-type">
                                    <option value="excl" @selected(old('value_vat_type', 'excl') === 'excl')>Excl. BTW</option>
                                    <option value="incl" @selected(old('value_vat_type') === 'incl')>Incl. BTW</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-5" data-quill-field data-ai-assist="true" data-ai-assist-context="opdrachtbeschrijving" data-ai-assist-url="{{ route('dashboard.ai-assist.text') }}">
                            <label for="description_editor" class="{{ $labelClass }}">Omschrijving van de opdracht</label>
                            <div id="description_editor" class="min-h-[220px] rounded-lg bg-white" data-quill-editor data-quill-required="true" data-quill-placeholder="Beschrijf de opdracht...">{!! $descriptionHtml ?? '' !!}</div>
                            <textarea id="description" name="description" class="hidden" data-quill-input data-preview-input="description">{{ old('description') }}</textarea>
                        </div>

                        <div id="confirmation-specification-accordion" class="mt-6" data-accordion="open">
                            @foreach ($specificationSections as $sectionKey => $section)
                                @php
                                    $sectionHasValue = collect($section['fields'])
                                        ->keys()
                                        ->contains(fn (string $fieldKey): bool => filled(data_get($specificationValues, $sectionKey.'.'.$fieldKey)));
                                @endphp

                                <h3 id="confirmation-specification-heading-{{ $sectionKey }}">
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between gap-3 border border-gray-200 bg-gray-50 p-4 text-left text-sm font-semibold text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-700 @if (! $loop->first) border-t-0 @endif @if ($loop->first) rounded-t-lg @endif"
                                        data-accordion-target="#confirmation-specification-body-{{ $sectionKey }}"
                                        aria-expanded="{{ $sectionHasValue ? 'true' : 'false' }}"
                                        aria-controls="confirmation-specification-body-{{ $sectionKey }}"
                                    >
                                        <span>{{ $section['label'] }}</span>
                                        <svg data-accordion-icon class="h-3 w-3 shrink-0 rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
                                        </svg>
                                    </button>
                                </h3>

                                <div
                                    id="confirmation-specification-body-{{ $sectionKey }}"
                                    class="@if (! $sectionHasValue) hidden @endif"
                                    aria-labelledby="confirmation-specification-heading-{{ $sectionKey }}"
                                >
                                    <div class="border border-t-0 border-gray-200 bg-white p-4 @if ($loop->last) rounded-b-lg @endif">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            @foreach ($section['fields'] as $fieldKey => $field)
                                                @php
                                                    $fieldId = 'specifications_'.$sectionKey.'_'.$fieldKey;
                                                    $fieldName = 'specifications['.$sectionKey.']['.$fieldKey.']';
                                                    $fieldValue = data_get($specificationValues, $sectionKey.'.'.$fieldKey);
                                                    $fieldType = $field['type'] ?? 'text';
                                                @endphp

                                                <div class="{{ $fieldType === 'textarea' ? 'md:col-span-2' : '' }}">
                                                    <label for="{{ $fieldId }}" class="{{ $labelClass }}">{{ $field['label'] }}</label>

                                                    @if ($fieldType === 'textarea')
                                                        <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" class="{{ $inputClass }}" rows="3">{{ $fieldValue }}</textarea>
                                                    @elseif ($fieldType === 'yes_no')
                                                        <select id="{{ $fieldId }}" name="{{ $fieldName }}" class="{{ $inputClass }}">
                                                            <option value="">Niet ingevuld</option>
                                                            <option value="yes" @selected($fieldValue === 'yes')>Ja</option>
                                                            <option value="no" @selected($fieldValue === 'no')>Nee</option>
                                                        </select>
                                                    @elseif ($fieldType === 'select')
                                                        <select id="{{ $fieldId }}" name="{{ $fieldName }}" class="{{ $inputClass }}">
                                                            <option value="">Niet ingevuld</option>
                                                            @foreach ($field['options'] as $optionValue => $optionLabel)
                                                                <option value="{{ $optionValue }}" @selected($fieldValue === $optionValue)>{{ $optionLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <input
                                                            id="{{ $fieldId }}"
                                                            name="{{ $fieldName }}"
                                                            class="{{ $inputClass }}"
                                                            type="{{ $fieldType }}"
                                                            value="{{ $fieldValue }}"
                                                            @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
                                                            @if ($fieldType === 'number') min="0" @endif
                                                        >
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <label for="attachment" class="{{ $labelClass }}">Bijlage</label>
                                <input id="attachment" name="attachment" type="file" class="{{ $fileClass }}" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                                <p class="mt-2 text-xs text-gray-500">Optioneel. PDF, Word, Excel of afbeelding tot 10 MB.</p>
                            </div>

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <label for="quote" class="{{ $labelClass }}">Offerte</label>
                                <input id="quote" name="quote" type="file" class="{{ $fileClass }}" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                                <p class="mt-2 text-xs text-gray-500">Optioneel. PDF, Word, Excel of afbeelding tot 10 MB.</p>
                            </div>
                        </div>

                        <section
                            class="mt-6 rounded-lg border border-blue-100 bg-blue-50 p-4"
                            data-ai-toolkit
                            data-ai-form-selector="#confirmation_create_form"
                            data-ai-generate-url="{{ route('dashboard.ai-assist.concept') }}"
                            data-ai-check-url="{{ route('dashboard.ai-assist.check') }}"
                        >
                            <h3 class="mb-3 text-base font-semibold text-gray-900">AI-assistent</h3>

                            <label for="ai_brief" class="{{ $labelClass }}">Korte opdracht</label>
                            <textarea
                                id="ai_brief"
                                class="{{ $inputClass }}"
                                data-ai-brief
                                rows="3"
                                placeholder="Bijvoorbeeld: website bouwen voor Acme, 3 pagina's, EUR 1.500 excl. btw, oplevering eind september."
                            ></textarea>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button" class="{{ $secondaryButtonClass }} px-3 py-2 text-xs" data-ai-generate>Concept maken</button>
                                <button type="button" class="{{ $secondaryButtonClass }} px-3 py-2 text-xs" data-ai-check>Compleetheid checken</button>
                            </div>

                            <p class="mt-3 min-h-5 text-sm text-gray-600" data-ai-status></p>
                            <div class="ai-check-results" data-ai-check-results hidden></div>
                        </section>

                        <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-gray-200 pt-4">
                            <button type="button" class="{{ $secondaryButtonClass }}" data-builder-close>Klaar met invullen</button>
                            <button type="submit" name="submit_action" value="send" class="{{ $primaryButtonClass }}">Verzenden</button>
                        </div>
                    </section>

                    <article class="mx-auto min-h-[1120px] w-full max-w-6xl rounded-lg border border-gray-200 bg-white p-6 text-gray-900 shadow-sm md:p-10" style="--preview-accent: {{ $user->primary_color ?: '#1e40af' }};" data-confirmation-preview>
                        <header class="mb-16 grid gap-8 md:grid-cols-[minmax(0,1fr)_minmax(220px,0.85fr)]">
                            <div class="flex min-h-20 items-center">
                                @if ($logoDataUri !== null)
                                    <img src="{{ $logoDataUri }}" alt="{{ $user->company_name }} logo" class="max-h-24 w-auto max-w-64 object-contain">
                                @else
                                    <span class="text-4xl font-extrabold leading-none text-blue-800">{{ $user->company_name ?: config('app.name') }}</span>
                                @endif
                            </div>

                            <div class="space-y-1 text-sm leading-6 text-gray-900">
                                <strong class="block font-bold">{{ $user->company_name ?: 'Jouw bedrijfsnaam' }}</strong>
                                @forelse ($senderAddressLines as $line)
                                    <span class="block">{{ $line }}</span>
                                @empty
                                    <span class="block">Bedrijfsadres nog niet ingevuld</span>
                                @endforelse
                                <span class="block">{{ $user->email }}</span>
                                @if (filled($user->kvk_number))
                                    <span class="block">KVK: {{ $user->kvk_number }}</span>
                                @endif
                            </div>
                        </header>

                        <section
                            class="mb-16 w-full max-w-sm cursor-pointer rounded-lg border border-l-blue-700 p-4 transition hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-200"
                            role="button"
                            tabindex="0"
                            data-builder-open="client"
                            aria-expanded="false"
                            aria-label="Opdrachtgever selecteren"
                        >
                            <p class="mb-2 text-sm font-bold text-blue-800">Opdrachtgever</p>
                            <strong class="block text-base font-bold text-gray-900" data-preview-target="client-company">{{ $selectedContact?->company_name ?: 'Nog te selecteren' }}</strong>
                            <span class="block text-sm text-gray-700" data-preview-target="client-person">{{ $selectedContact?->contactName() ?: 'Contactpersoon' }}</span>
                            <span class="block text-sm text-gray-700" data-preview-target="client-email">{{ $selectedContact?->contact_email ?: 'E-mailadres opdrachtgever' }}</span>
                            <span class="block text-sm text-gray-700" data-preview-target="client-address">
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
                            class="block cursor-pointer rounded-lg p-4 transition hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-200"
                            role="button"
                            tabindex="0"
                            data-builder-open="confirmation"
                            aria-expanded="false"
                            aria-label="Opdrachtbevestiging invullen"
                        >
                            <section class="mb-8 grid gap-8 md:grid-cols-[minmax(0,1fr)_minmax(220px,auto)]">
                                <div>
                                    <p class="mb-2 text-sm font-bold text-blue-800">Concept</p>
                                    <h2 class="m-0 text-4xl font-light leading-tight text-gray-900 md:text-5xl">
                                        <span data-preview-target="title">{{ old('title') ?: 'Opdrachtbevestiging' }}</span>
                                        <span class="block text-gray-400">Concept</span>
                                    </h2>
                                </div>

                                <dl class="grid content-start gap-3 text-sm">
                                    <div class="grid grid-cols-[120px_minmax(0,1fr)] gap-4">
                                        <dt class="font-bold text-gray-600">Aanmaakdatum</dt>
                                        <dd class="m-0 text-gray-900">{{ now()->format('d-m-Y') }}</dd>
                                    </div>
                                    <div class="grid grid-cols-[120px_minmax(0,1fr)] gap-4">
                                        <dt class="font-bold text-gray-600">Startdatum</dt>
                                        <dd class="m-0 text-gray-900" data-preview-target="agreement-date">{{ $startDate ?: 'Nog niet ingevuld' }}</dd>
                                    </div>
                                </dl>
                            </section>

                            <section class="border-t border-gray-200 py-8">
                                <div class="mb-4 flex items-baseline justify-between gap-4">
                                    <p class="mb-0 text-sm font-bold text-blue-800">Omschrijving</p>
                                    <span class="text-sm font-bold text-gray-400">1. Opdracht beschrijven</span>
                                </div>
                                <div class="text-sm leading-7 text-gray-800" data-preview-target="description">
                                    @if ($descriptionHtml !== null)
                                        {!! $descriptionHtml !!}
                                    @else
                                        <p class="mb-0 text-sm text-gray-800">Beschrijf hier de opdracht, werkzaamheden, verwachtingen en oplevering.</p>
                                    @endif
                                </div>
                            </section>

                            <section class="border-t border-gray-200 py-8">
                                <div class="mb-4 flex items-baseline justify-between gap-4">
                                    <p class="mb-0 text-sm font-bold text-blue-800">Afspraken</p>
                                    <span class="text-sm font-bold text-gray-400">2. Velden aanvullen</span>
                                </div>

                                <div class="grid border-l border-t border-gray-200 md:grid-cols-2">
                                    <div class="min-h-20 border-b border-r border-gray-200 p-4">
                                        <span class="mb-1 block text-xs font-bold text-gray-400">Kenmerk opdrachtgever</span>
                                        <strong class="block text-sm font-bold text-gray-900" data-preview-target="client-reference">{{ $clientReference ?: 'Nog niet ingevuld' }}</strong>
                                    </div>
                                    <div class="min-h-20 border-b border-r border-gray-200 p-4">
                                        <span class="mb-1 block text-xs font-bold text-gray-400">Projectnaam</span>
                                        <strong class="block text-sm font-bold text-gray-900" data-preview-target="project-name">{{ $projectName ?: 'Nog niet ingevuld' }}</strong>
                                    </div>
                                    <div class="min-h-20 border-b border-r border-gray-200 p-4">
                                        <span class="mb-1 block text-xs font-bold text-gray-400">Locatie werkzaamheden</span>
                                        <strong class="block text-sm font-bold text-gray-900" data-preview-target="work-location">{{ $workLocation ?: 'Nog niet ingevuld' }}</strong>
                                    </div>
                                    <div class="min-h-20 border-b border-r border-gray-200 p-4">
                                        <span class="mb-1 block text-xs font-bold text-gray-400">Duur van de opdracht</span>
                                        <strong class="block text-sm font-bold text-gray-900" data-preview-target="duration">{{ old('duration') ?: 'Nog niet ingevuld' }}</strong>
                                    </div>
                                    <div class="min-h-20 border-b border-r border-gray-200 p-4">
                                        <span class="mb-1 block text-xs font-bold text-gray-400">Periode</span>
                                        <strong class="block text-sm font-bold text-gray-900">
                                            <span data-preview-target="start-date">{{ $startDate ?: 'Startdatum' }}</span>
                                            <span> - </span>
                                            <span data-preview-target="end-date">{{ $endDate ?: 'Einddatum' }}</span>
                                        </strong>
                                    </div>
                                    <div class="min-h-20 border-b border-r border-gray-200 p-4">
                                        <span class="mb-1 block text-xs font-bold text-gray-400">Tarief</span>
                                        <strong class="block text-sm font-bold text-gray-900">
                                            <span data-preview-target="rate">{{ $rate ?: '0,00' }}</span>
                                            <span data-preview-target="rate-unit">{{ $rateUnit ?: '' }}</span>
                                        </strong>
                                    </div>
                                    <div class="min-h-20 border-b border-r border-gray-200 p-4">
                                        <span class="mb-1 block text-xs font-bold text-gray-400">Vergoeding</span>
                                        <strong class="block text-sm font-bold text-gray-900">
                                            <span data-preview-target="total-value">{{ $totalValue ? 'EUR '.number_format((float) $totalValue, 2, ',', '.') : 'EUR 0,00' }}</span>
                                            <span data-preview-target="vat-type">{{ old('value_vat_type', 'excl') === 'incl' ? 'incl. BTW' : 'excl. BTW' }}</span>
                                        </strong>
                                    </div>
                                    <div class="min-h-20 border-b border-r border-gray-200 p-4">
                                        <span class="mb-1 block text-xs font-bold text-gray-400">BTW-percentage</span>
                                        <strong class="block text-sm font-bold text-gray-900" data-preview-target="vat-percentage">{{ $vatPercentage ?: '21' }}%</strong>
                                    </div>
                                </div>
                            </section>

                            <section class="border-t border-gray-200 pt-8">
                                <div class="mb-4 flex items-baseline justify-between gap-4">
                                    <p class="mb-0 text-sm font-bold text-blue-800">Vaste afspraken</p>
                                    <span class="text-sm font-bold text-gray-400">3. Automatisch toegevoegd</span>
                                </div>

                                @if ($defaultAgreementsHtml !== '')
                                    <div class="text-sm leading-7 text-gray-800">{!! $defaultAgreementsHtml !!}</div>
                                @else
                                    <p class="mb-0 text-sm leading-7 text-gray-800">Je basis afspraken en algemene voorwaarden worden hier meegenomen zodra je die in Mijn profiel hebt ingesteld.</p>
                                @endif
                            </section>
                        </div>

                        <footer class="mt-8 flex flex-wrap justify-between gap-3 border-t-2 border-gray-900 pt-4 text-xs text-gray-500">
                            <span>Opgesteld door {{ $user->company_name ?: $senderName ?: config('app.name') }}</span>
                            <span data-preview-target="client-reference-footer">{{ $clientReference ? 'Kenmerk '.$clientReference : 'Concept zonder kenmerk' }}</span>
                        </footer>
                    </article>
                </div>

                <aside class="flex w-full flex-col gap-4 self-start xl:sticky xl:top-24 xl:w-80 xl:min-w-80 xl:max-w-80 xl:shrink-0">
                    <section class="w-full rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <span class="mb-4 inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">Concept</span>
                        <dl class="divide-y divide-gray-200 text-sm">
                            <div class="py-3">
                                <dt class="font-medium text-gray-500">Opdrachtgever</dt>
                                <dd class="mt-1 break-words text-gray-900" data-preview-target="aside-client">{{ $selectedContact?->company_name ?: 'Nog niet geselecteerd' }}</dd>
                            </div>
                            <div class="py-3">
                                <dt class="font-medium text-gray-500">Workflow</dt>
                                <dd class="mt-1 text-gray-900">Standaard</dd>
                            </div>
                            <div class="py-3">
                                <dt class="font-medium text-gray-500">Datum</dt>
                                <dd class="mt-1 text-gray-900">{{ now()->translatedFormat('j F Y') }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5 flex flex-col gap-2">
                            <button
                                type="submit"
                                name="submit_action"
                                value="test"
                                class="block min-h-0 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-center text-sm font-medium leading-5 text-gray-900 hover:bg-gray-100 hover:text-blue-800 focus:outline-none focus:ring-4 focus:ring-gray-100"
                            >
                                Verzend test
                            </button>
                            <button
                                type="submit"
                                name="submit_action"
                                value="send"
                                class="block min-h-0 w-full rounded-lg bg-blue-800 px-4 py-2.5 text-center text-sm font-medium leading-5 text-white hover:bg-blue-900 focus:outline-none focus:ring-4 focus:ring-blue-300"
                            >
                                Verzenden
                            </button>
                        </div>
                    </section>

                    <section class="w-full rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-2 text-base font-semibold text-gray-900">Vaste gegevens</h2>
                        <p class="mb-4 break-words text-sm leading-6 text-gray-600">Bedrijfsgegevens, basis afspraken, logo en huisstijl worden automatisch toegevoegd aan de PDF.</p>
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('dashboard.profile.brand') }}" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-center text-xs font-medium leading-5 text-gray-900 hover:bg-gray-100 hover:text-blue-800 focus:outline-none focus:ring-4 focus:ring-gray-100">Logo & huisstijl</a>
                            <a href="{{ route('dashboard.profile.agreements') }}" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-center text-xs font-medium leading-5 text-gray-900 hover:bg-gray-100 hover:text-blue-800 focus:outline-none focus:ring-4 focus:ring-gray-100">Basis afspraken</a>
                        </div>
                    </section>
                </aside>
            </div>
        </form>
    @endif
@endsection
