<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Quote extends Model
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
        'total_value',
        'value_vat_type',
        'valid_until',
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
        'attachment_path',
        'attachment_original_name',
        'attachment_mime_type',
        'pdf_path',
        'pdf_original_name',
        'pdf_mime_type',
        'pdf_generated_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'total_value' => 'decimal:2',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'pdf_generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function descriptionHtml(): string
    {
        return Confirmation::sanitizeDescription($this->description) ?? 'Geen omschrijving toegevoegd.';
    }

    public function descriptionText(): string
    {
        return Confirmation::richTextToPlainText($this->descriptionHtml());
    }

    public function valueVatLabel(): string
    {
        return $this->value_vat_type === 'incl' ? 'incl. BTW' : 'excl. BTW';
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

    public function canBeMarked(): bool
    {
        return $this->status === 'verzonden';
    }

    /**
     * @return array<int, array{label: string, path: string, name: string, mime: string|null}>
     */
    public function emailAttachments(): array
    {
        return array_values(array_filter([
            $this->pdf_path ? [
                'label' => 'Offerte',
                'path' => $this->pdf_path,
                'name' => $this->pdf_original_name ?: $this->pdfDownloadName(),
                'mime' => $this->pdf_mime_type ?: 'application/pdf',
            ] : null,
            $this->attachment_path ? [
                'label' => 'Bijlage',
                'path' => $this->attachment_path,
                'name' => $this->attachment_original_name ?: basename($this->attachment_path),
                'mime' => $this->attachment_mime_type,
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
        $reference = Str::slug($this->reference) ?: 'offerte';

        return 'offerte-'.$reference.'.pdf';
    }
}
