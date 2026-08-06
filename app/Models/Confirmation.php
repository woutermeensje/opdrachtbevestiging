<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Confirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_id',
        'reference',
        'title',
        'client_name',
        'client_contact_name',
        'client_email',
        'client_kvk_number',
        'description',
        'specifications',
        'total_value',
        'value_vat_type',
        'public_token',
        'status',
        'sender_name',
        'sender_email',
        'sender_company_name',
        'sender_kvk_number',
        'sender_street_name',
        'sender_house_number',
        'sender_house_number_addition',
        'sender_postal_code',
        'sender_city',
        'sender_country',
        'sender_company_logo_path',
        'sender_company_logo_original_name',
        'sender_company_logo_mime_type',
        'terms_path',
        'terms_original_name',
        'terms_mime_type',
        'default_agreements',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime_type',
        'quote_path',
        'quote_original_name',
        'quote_mime_type',
        'pdf_path',
        'pdf_original_name',
        'pdf_mime_type',
        'pdf_generated_at',
        'agreement_date',
        'duration',
        'sent_at',
        'signed_at',
        'expires_at',
        'viewed_at',
        'signer_name',
        'signer_ip',
        'signer_user_agent',
        'signer_signature_path',
        'signer_signature_mime_type',
    ];

    protected function casts(): array
    {
        return [
            'total_value' => 'decimal:2',
            'specifications' => 'array',
            'agreement_date' => 'date',
            'sent_at' => 'datetime',
            'signed_at' => 'datetime',
            'expires_at' => 'date',
            'viewed_at' => 'datetime',
            'pdf_generated_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, array{label: string, fields: array<string, array<string, mixed>>}>
     */
    public static function specificationSections(): array
    {
        return [
            'general' => [
                'label' => 'Algemene gegevens',
                'fields' => [
                    'assignment_number' => ['label' => 'Opdrachtnummer', 'type' => 'text'],
                    'client_reference' => ['label' => 'Referentie opdrachtgever', 'type' => 'text'],
                    'project_name' => ['label' => 'Projectnaam', 'type' => 'text'],
                    'work_location' => ['label' => 'Locatie werkzaamheden', 'type' => 'text'],
                    'department' => ['label' => 'Afdeling', 'type' => 'text'],
                    'cost_center' => ['label' => 'Kostenplaats', 'type' => 'text'],
                ],
            ],
            'planning' => [
                'label' => 'Planning',
                'fields' => [
                    'start_date' => ['label' => 'Startdatum', 'type' => 'date'],
                    'end_date' => ['label' => 'Einddatum', 'type' => 'date'],
                    'expected_duration' => ['label' => 'Verwachte duur', 'type' => 'text'],
                    'total_hours' => ['label' => 'Totaal aantal uren', 'type' => 'number', 'step' => '0.25'],
                    'workdays' => ['label' => 'Werkdagen', 'type' => 'text'],
                    'working_hours' => ['label' => 'Werktijden', 'type' => 'text'],
                    'deadline' => ['label' => 'Deadline', 'type' => 'date'],
                    'extension_possible' => ['label' => 'Mogelijkheid tot verlenging', 'type' => 'yes_no'],
                ],
            ],
            'financial' => [
                'label' => 'Financiële afspraken',
                'fields' => [
                    'rate' => ['label' => 'Tarief', 'type' => 'number', 'step' => '0.01'],
                    'rate_unit' => [
                        'label' => 'Tariefeenheid',
                        'type' => 'select',
                        'options' => [
                            'per_hour' => 'Per uur',
                            'per_day' => 'Per dag',
                            'per_week' => 'Per week',
                            'per_month' => 'Per maand',
                            'fixed_amount' => 'Vast bedrag',
                            'per_item' => 'Per stuk',
                            'per_project' => 'Per project',
                            'per_kilometer' => 'Per kilometer',
                        ],
                    ],
                    'vat_percentage' => ['label' => 'BTW-percentage', 'type' => 'number', 'step' => '0.01'],
                    'total_amount' => ['label' => 'Totaalbedrag', 'type' => 'number', 'step' => '0.01'],
                    'maximum_budget' => ['label' => 'Maximum budget', 'type' => 'number', 'step' => '0.01'],
                    'currency' => ['label' => 'Valuta', 'type' => 'text'],
                    'payment_term' => ['label' => 'Betaaltermijn', 'type' => 'text'],
                    'invoice_frequency' => [
                        'label' => 'Factuurfrequentie',
                        'type' => 'select',
                        'options' => [
                            'weekly' => 'Wekelijks',
                            'monthly' => 'Maandelijks',
                            'after_delivery' => 'Na oplevering',
                            'per_phase' => 'Per fase',
                        ],
                    ],
                    'additional_work_allowed' => ['label' => 'Meerwerk toegestaan', 'type' => 'yes_no'],
                    'additional_work_rate' => ['label' => 'Meerwerktarief', 'type' => 'text'],
                    'advance_payment' => ['label' => 'Voorschot', 'type' => 'text'],
                ],
            ],
            'travel_expenses' => [
                'label' => 'Reiskosten',
                'fields' => [
                    'compensation' => ['label' => 'Reiskostenvergoeding', 'type' => 'yes_no'],
                    'mileage_compensation' => ['label' => 'Kilometervergoeding', 'type' => 'text'],
                    'public_transport_compensation' => ['label' => 'OV-vergoeding', 'type' => 'text'],
                    'parking_costs' => ['label' => 'Parkeerkosten', 'type' => 'text'],
                    'hotel_costs' => ['label' => 'Hotelkosten', 'type' => 'text'],
                    'meal_allowance' => ['label' => 'Maaltijdvergoeding', 'type' => 'text'],
                    'other_expenses' => ['label' => 'Overige onkosten', 'type' => 'textarea'],
                    'maximum_claim_amount' => ['label' => 'Maximum declaratiebedrag', 'type' => 'number', 'step' => '0.01'],
                ],
            ],
            'materials' => [
                'label' => 'Materialen',
                'fields' => [
                    'laptop_provided' => ['label' => 'Laptop verstrekt', 'type' => 'yes_no'],
                    'phone_provided' => ['label' => 'Telefoon verstrekt', 'type' => 'yes_no'],
                    'car_provided' => ['label' => 'Auto verstrekt', 'type' => 'yes_no'],
                    'clothing_ppe' => ['label' => "Kleding/PBM's", 'type' => 'text'],
                    'software_licenses' => ['label' => 'Softwarelicenties', 'type' => 'textarea'],
                    'access_pass' => ['label' => 'Toegangspas', 'type' => 'yes_no'],
                    'keys' => ['label' => 'Sleutels', 'type' => 'yes_no'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function specificationValidationRules(string $root = 'specifications'): array
    {
        $rules = [
            $root => ['nullable', 'array'],
        ];

        foreach (self::specificationSections() as $sectionKey => $section) {
            $rules[$root.'.'.$sectionKey] = ['nullable', 'array'];

            foreach ($section['fields'] as $fieldKey => $field) {
                $fieldRules = ['nullable'];

                if (($field['type'] ?? 'text') === 'date') {
                    $fieldRules[] = 'date';
                } elseif (($field['type'] ?? 'text') === 'number') {
                    $fieldRules[] = 'numeric';
                    $fieldRules[] = 'min:0';
                } elseif (($field['type'] ?? 'text') === 'yes_no') {
                    $fieldRules[] = 'in:yes,no';
                } elseif (($field['type'] ?? 'text') === 'select') {
                    $fieldRules[] = 'in:'.implode(',', array_keys($field['options'] ?? []));
                } else {
                    $fieldRules[] = 'string';
                    $fieldRules[] = ($field['type'] ?? 'text') === 'textarea' ? 'max:2000' : 'max:255';
                }

                $rules[$root.'.'.$sectionKey.'.'.$fieldKey] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>|null  $input
     * @return array<string, array<string, string>>
     */
    public static function normalizeSpecifications(?array $input): array
    {
        $normalized = [];

        foreach (self::specificationSections() as $sectionKey => $section) {
            foreach ($section['fields'] as $fieldKey => $field) {
                $value = data_get($input, $sectionKey.'.'.$fieldKey);

                if ($value === null || $value === '') {
                    continue;
                }

                $value = is_string($value) ? trim(strip_tags($value)) : (string) $value;

                if ($value === '') {
                    continue;
                }

                $normalized[$sectionKey][$fieldKey] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @return array<int, array{label: string, fields: array<int, array{label: string, value: string, multiline: bool}>}>
     */
    public function filledSpecificationSections(): array
    {
        $sections = [];
        $values = $this->specifications ?? [];

        foreach (self::specificationSections() as $sectionKey => $section) {
            $fields = [];

            foreach ($section['fields'] as $fieldKey => $field) {
                $value = data_get($values, $sectionKey.'.'.$fieldKey);

                if ($value === null || $value === '') {
                    continue;
                }

                $fields[] = [
                    'label' => $field['label'],
                    'value' => self::formatSpecificationValue($value, $field),
                    'multiline' => ($field['type'] ?? 'text') === 'textarea',
                ];
            }

            if ($fields !== []) {
                $sections[] = [
                    'label' => $section['label'],
                    'fields' => $fields,
                ];
            }
        }

        return $sections;
    }

    public function hasSpecifications(): bool
    {
        return $this->filledSpecificationSections() !== [];
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private static function formatSpecificationValue(mixed $value, array $field): string
    {
        $type = $field['type'] ?? 'text';

        if ($type === 'yes_no') {
            return $value === 'yes' ? 'Ja' : 'Nee';
        }

        if ($type === 'select') {
            return $field['options'][$value] ?? (string) $value;
        }

        if ($type === 'date') {
            try {
                return Carbon::parse($value)->format('d-m-Y');
            } catch (\Throwable) {
                return (string) $value;
            }
        }

        return (string) $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public static function sanitizeDescription(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $clean = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $description) ?? '';
        $clean = strip_tags($clean, '<p><br><strong><b><em><i><u><ul><ol><li>');
        $clean = preg_replace('/<([a-z][a-z0-9]*)\b[^>]*>/i', '<$1>', $clean) ?? '';
        $clean = trim($clean);

        return $clean !== '' ? $clean : null;
    }

    public function descriptionHtml(): string
    {
        return self::sanitizeDescription($this->description) ?? 'Geen omschrijving toegevoegd.';
    }

    public function descriptionText(): string
    {
        return self::richTextToPlainText($this->descriptionHtml());
    }

    public function defaultAgreementsHtml(): string
    {
        return self::sanitizeDescription($this->default_agreements) ?? '';
    }

    public function defaultAgreementsText(): string
    {
        return self::richTextToPlainText($this->defaultAgreementsHtml());
    }

    public function valueVatLabel(): string
    {
        return $this->value_vat_type === 'incl' ? 'incl. BTW' : 'excl. BTW';
    }

    public static function richTextToPlainText(?string $html): string
    {
        $html = str_replace(
            ['<br>', '<br/>', '<br />', '</p>', '</li>'],
            ["\n", "\n", "\n", "\n\n", "\n"],
            $html ?? '',
        );

        return trim(html_entity_decode(strip_tags($html)));
    }

    public function senderCompanyDisplayName(): string
    {
        return $this->sender_company_name
            ?: $this->user?->company_name
            ?: $this->sender_name
            ?: 'Opdrachtnemer';
    }

    /**
     * @return array<int, string>
     */
    public function senderAddressLines(): array
    {
        $streetLine = trim(implode(' ', array_filter([
            $this->sender_street_name,
            $this->sender_house_number,
            $this->sender_house_number_addition,
        ])));

        $cityLine = trim(implode(' ', array_filter([
            $this->sender_postal_code,
            $this->sender_city,
        ])));

        return array_values(array_filter([
            $streetLine,
            $cityLine,
            $this->sender_country,
        ]));
    }

    public function senderCompanyLogoDataUri(): ?string
    {
        if (! filled($this->sender_company_logo_path) || ! Storage::disk('local')->exists($this->sender_company_logo_path)) {
            return null;
        }

        $contents = Storage::disk('local')->get($this->sender_company_logo_path);
        $mimeType = $this->sender_company_logo_mime_type ?: 'image/png';

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }

    public function signerSignatureDataUri(): ?string
    {
        if (! filled($this->signer_signature_path) || ! Storage::disk('local')->exists($this->signer_signature_path)) {
            return null;
        }

        $contents = Storage::disk('local')->get($this->signer_signature_path);
        $mimeType = $this->signer_signature_mime_type ?: 'image/png';

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }

    public function publicUrl(): string
    {
        return URL::route('confirmations.public.show', $this->public_token);
    }

    public function canBeRetracted(): bool
    {
        return $this->status === 'verzonden' && $this->signed_at === null;
    }

    /**
     * @return array<int, array{label: string, path: string, name: string, mime: string|null}>
     */
    public function emailAttachments(): array
    {
        return array_values(array_filter([
            $this->pdf_path ? [
                'label' => 'Opdrachtbevestiging',
                'path' => $this->pdf_path,
                'name' => $this->pdf_original_name ?: $this->pdfDownloadName(),
                'mime' => $this->pdf_mime_type ?: 'application/pdf',
            ] : null,
            $this->terms_path ? [
                'label' => 'Algemene voorwaarden',
                'path' => $this->terms_path,
                'name' => $this->terms_original_name ?: basename($this->terms_path),
                'mime' => $this->terms_mime_type,
            ] : null,
            $this->attachment_path ? [
                'label' => 'Bijlage',
                'path' => $this->attachment_path,
                'name' => $this->attachment_original_name ?: basename($this->attachment_path),
                'mime' => $this->attachment_mime_type,
            ] : null,
            $this->quote_path ? [
                'label' => 'Offerte',
                'path' => $this->quote_path,
                'name' => $this->quote_original_name ?: basename($this->quote_path),
                'mime' => $this->quote_mime_type,
            ] : null,
        ]));
    }

    public function hasEmailAttachments(): bool
    {
        return $this->emailAttachments() !== [];
    }

    public function emailAttachmentSummary(): string
    {
        return collect($this->emailAttachments())
            ->map(fn (array $attachment): string => $attachment['label'].': '.$attachment['name'])
            ->implode(', ');
    }

    public function hasPdf(): bool
    {
        return filled($this->pdf_path) && Storage::disk('local')->exists($this->pdf_path);
    }

    public function pdfDownloadName(): string
    {
        $reference = Str::slug($this->reference) ?: 'opdrachtbevestiging';

        return 'opdrachtbevestiging-'.$reference.'.pdf';
    }
}
