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

                    <div class="rich-editor-field" data-rich-editor-wrapper>
                        <label for="description_editor">Tekstblok</label>
                        <div class="rich-editor-toolbar" aria-label="Opmaak">
                            <button type="button" data-rich-command="bold"><strong>B</strong></button>
                            <button type="button" data-rich-command="italic"><em>I</em></button>
                            <button type="button" data-rich-command="underline"><u>U</u></button>
                            <button type="button" data-rich-command="insertUnorderedList">Lijst</button>
                            <button type="button" data-rich-command="insertOrderedList">1. Lijst</button>
                        </div>
                        <div id="description_editor" class="rich-editor" contenteditable="true" data-rich-editor data-rich-required="true">'.(\App\Models\Confirmation::sanitizeDescription(old('description')) ?? '').'</div>
                        <textarea id="description" name="description" class="rich-editor-input" data-rich-editor-input>'.e(old('description')).'</textarea>
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
                        <button type="submit" class="btn btn-primary">Verzenden</button>
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
