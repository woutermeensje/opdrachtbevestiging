@extends('layouts.dashboard', [
    'title' => 'Aanmaken',
])

@php
    $user = auth()->user();
    $senderAddressLines = $user->companyAddressLines();
    $companyLogo = $user->companyLogoDataUri();
    $selectedContact = $contacts->firstWhere('id', (int) old('contact_id'));
    $selectedContactAddressLines = $selectedContact ? array_values(array_filter([
        trim(implode(' ', array_filter([
            $selectedContact->street_name,
            $selectedContact->house_number,
            $selectedContact->house_number_addition,
        ]))),
        trim(implode(' ', array_filter([
            $selectedContact->postal_code,
            $selectedContact->city,
        ]))),
        $selectedContact->country,
    ])) : [];
    $defaultAgreements = $user->defaultAgreementsHtml();
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Aanmaken',
        'title' => 'Nieuwe opdrachtbevestiging',
        'text' => 'Stel de opdrachtbevestiging op in documentvorm en verstuur hem daarna direct naar de opdrachtgever.',
    ])

    @include('partials.forms.errors')

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    @if ($contacts->isEmpty())
        @include('partials.dashboard.panel', [
            'title' => 'Voeg eerst een opdrachtgever toe',
            'slot' => '
                <p>Je hebt nog geen opdrachtgever in je account staan.</p>
                <p><a href="'.e(route('dashboard.contacts.create')).'" class="btn btn-primary">Opdrachtgever toevoegen</a></p>
            ',
        ])
    @else
        <form method="POST" action="{{ route('dashboard.create.store') }}" class="dashboard-document-form" enctype="multipart/form-data" data-confirmation-document-form>
            @csrf

            <article class="confirmation-document-card">
                <header class="confirmation-document-header">
                    <div class="confirmation-document-logo">
                        @if ($companyLogo !== null)
                            <img src="{{ $companyLogo }}" alt="">
                        @else
                            <span>Bedrijfslogo</span>
                        @endif
                    </div>

                    <div class="confirmation-document-parties">
                        <section class="confirmation-party">
                            <p class="confirmation-party-label">Verzender</p>
                            <p><strong>{{ $user->company_name }}</strong></p>
                            @foreach ($senderAddressLines as $line)
                                <p>{{ $line }}</p>
                            @endforeach
                            @if (filled($user->kvk_number))
                                <p>KVK: {{ $user->kvk_number }}</p>
                            @endif
                            <p>{{ trim($user->first_name.' '.$user->last_name) }}</p>
                            <p>{{ $user->email }}</p>
                        </section>

                        <section class="confirmation-party">
                            <label for="contact_id" class="confirmation-party-label">Ontvanger</label>
                            <select id="contact_id" name="contact_id" data-contact-select required>
                                <option value="">Kies een opdrachtgever</option>
                                @foreach ($contacts as $contact)
                                    @php
                                        $contactAddressLines = array_values(array_filter([
                                            trim(implode(' ', array_filter([
                                                $contact->street_name,
                                                $contact->house_number,
                                                $contact->house_number_addition,
                                            ]))),
                                            trim(implode(' ', array_filter([
                                                $contact->postal_code,
                                                $contact->city,
                                            ]))),
                                            $contact->country,
                                        ]));
                                    @endphp
                                    <option
                                        value="{{ $contact->id }}"
                                        data-company="{{ $contact->company_name }}"
                                        data-name="{{ $contact->contactName() }}"
                                        data-email="{{ $contact->contact_email }}"
                                        data-kvk="{{ $contact->kvk_number }}"
                                        data-address="{{ implode("\n", $contactAddressLines) }}"
                                        @selected(old('contact_id') == $contact->id)
                                    >
                                        {{ $contact->company_name }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="confirmation-recipient-preview" data-contact-preview>
                                @if ($selectedContact)
                                    <p><strong>{{ $selectedContact->company_name }}</strong></p>
                                    <p>{{ $selectedContact->contactName() ?: '-' }}</p>
                                    @foreach ($selectedContactAddressLines as $line)
                                        <p>{{ $line }}</p>
                                    @endforeach
                                    <p>{{ $selectedContact->contact_email }}</p>
                                    @if (filled($selectedContact->kvk_number))
                                        <p>KVK: {{ $selectedContact->kvk_number }}</p>
                                    @endif
                                @else
                                    <p class="confirmation-placeholder">Kies een opdrachtgever</p>
                                @endif
                            </div>
                        </section>
                    </div>
                </header>

                <section class="confirmation-document-section confirmation-document-title-section">
                    <label for="title">Titel opdrachtbevestiging</label>
                    <input id="title" name="title" class="confirmation-document-title-input" type="text" value="{{ old('title') }}" placeholder="Opdrachtbevestiging" required>
                </section>

                <section class="confirmation-document-section">
                    <div class="quill-field confirmation-document-editor" data-quill-field>
                        <label for="description_editor">Inhoud opdrachtbevestiging</label>
                        <div id="description_editor" class="quill-editor" data-quill-editor data-quill-required="true" data-quill-placeholder="Beschrijf de opdracht...">{!! \App\Models\Confirmation::sanitizeDescription(old('description')) ?? '' !!}</div>
                        <textarea id="description" name="description" class="quill-editor-input" data-quill-input>{{ old('description') }}</textarea>
                    </div>
                </section>

                <section class="confirmation-document-section">
                    <h2>Basis afspraken</h2>
                    @if ($defaultAgreements !== '')
                        <div class="dashboard-rich-content">{!! $defaultAgreements !!}</div>
                    @else
                        <p class="confirmation-placeholder">Nog geen basis afspraken ingesteld.</p>
                    @endif
                </section>
            </article>

            <section class="confirmation-upload-panel">
                <h2>Uploads</h2>
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
            </section>

            <div class="actions actions-end confirmation-document-actions">
                <button type="submit" class="btn btn-primary">Verzenden</button>
            </div>
        </form>
    @endif
@endsection
