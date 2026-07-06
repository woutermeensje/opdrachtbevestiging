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

    <div class="dashboard-create-layout">
        @include('partials.dashboard.panel', [
            'title' => 'Gegevens invullen',
            'class' => 'dashboard-create-form-panel',
            'slot' => '
                <form method="POST" action="'.e(route('dashboard.create.store')).'" class="dashboard-form">
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

                    <div class="actions actions-end">
                        <button type="submit" class="btn btn-primary">Verzenden</button>
                    </div>
                </form>
            ',
        ])

        @include('partials.dashboard.panel', [
            'title' => 'Na het opslaan',
            'class' => 'dashboard-create-side-panel',
            'slot' => '
                <p>Controleer de opdrachtbevestiging na het opslaan in het detailoverzicht.</p>
                <p>Vanuit daar verstuur je de volledige opdrachtbevestiging als e-mailtekst naar de opdrachtgever.</p>
            ',
        ])
    </div>
    @endif
@endsection
