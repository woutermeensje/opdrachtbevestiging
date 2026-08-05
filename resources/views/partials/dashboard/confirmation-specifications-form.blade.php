@php
    $specificationSections = \App\Models\Confirmation::specificationSections();
    $oldSpecifications = old('specifications', []);
@endphp

<div class="confirmation-specifications">
    @foreach ($specificationSections as $sectionKey => $section)
        @php
            $sectionHasValue = collect($section['fields'])
                ->keys()
                ->contains(fn (string $fieldKey): bool => filled(data_get($oldSpecifications, $sectionKey.'.'.$fieldKey)));
        @endphp

        <details class="confirmation-specification-section" @if ($sectionHasValue) open @endif>
            <summary>{{ $section['label'] }}</summary>

            <div class="confirmation-specification-grid">
                @foreach ($section['fields'] as $fieldKey => $field)
                    @php
                        $fieldId = 'specifications_'.$sectionKey.'_'.$fieldKey;
                        $fieldName = 'specifications['.$sectionKey.']['.$fieldKey.']';
                        $fieldValue = old('specifications.'.$sectionKey.'.'.$fieldKey);
                        $fieldType = $field['type'] ?? 'text';
                    @endphp

                    <div class="{{ $fieldType === 'textarea' ? 'confirmation-specification-field-wide' : '' }}">
                        <label for="{{ $fieldId }}">{{ $field['label'] }}</label>

                        @if ($fieldType === 'textarea')
                            <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" rows="3">{{ $fieldValue }}</textarea>
                        @elseif ($fieldType === 'yes_no')
                            <select id="{{ $fieldId }}" name="{{ $fieldName }}">
                                <option value="">Niet ingevuld</option>
                                <option value="yes" @selected($fieldValue === 'yes')>Ja</option>
                                <option value="no" @selected($fieldValue === 'no')>Nee</option>
                            </select>
                        @elseif ($fieldType === 'select')
                            <select id="{{ $fieldId }}" name="{{ $fieldName }}">
                                <option value="">Niet ingevuld</option>
                                @foreach ($field['options'] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected($fieldValue === $optionValue)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        @else
                            <input
                                id="{{ $fieldId }}"
                                name="{{ $fieldName }}"
                                type="{{ $fieldType }}"
                                value="{{ $fieldValue }}"
                                @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
                                @if ($fieldType === 'number') min="0" @endif
                            >
                        @endif
                    </div>
                @endforeach
            </div>
        </details>
    @endforeach
</div>
