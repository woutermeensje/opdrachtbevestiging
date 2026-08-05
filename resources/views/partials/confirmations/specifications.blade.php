@php
    $specificationSections = $confirmation->filledSpecificationSections();
@endphp

<div class="confirmation-specifications-summary">
    @foreach ($specificationSections as $section)
        <section class="confirmation-specifications-summary-section">
            <h3>{{ $section['label'] }}</h3>
            <table class="confirmation-specifications-table">
                <tbody>
                    @foreach ($section['fields'] as $field)
                        <tr>
                            <th>{{ $field['label'] }}</th>
                            <td>
                                @if ($field['multiline'])
                                    {!! nl2br(e($field['value'])) !!}
                                @else
                                    {{ $field['value'] }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endforeach
</div>
