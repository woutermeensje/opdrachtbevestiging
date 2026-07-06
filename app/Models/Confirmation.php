<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

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
        'total_value',
        'public_token',
        'status',
        'sender_name',
        'sender_email',
        'agreement_date',
        'sent_at',
        'signed_at',
        'expires_at',
        'viewed_at',
        'signer_name',
        'signer_ip',
        'signer_user_agent',
    ];

    protected function casts(): array
    {
        return [
            'total_value' => 'decimal:2',
            'agreement_date' => 'date',
            'sent_at' => 'datetime',
            'signed_at' => 'datetime',
            'expires_at' => 'date',
            'viewed_at' => 'datetime',
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
        $html = str_replace(
            ['<br>', '<br/>', '<br />', '</p>', '</li>'],
            ["\n", "\n", "\n", "\n\n", "\n"],
            $this->descriptionHtml(),
        );

        return trim(html_entity_decode(strip_tags($html)));
    }

    public function publicUrl(): string
    {
        return URL::route('confirmations.public.show', $this->public_token);
    }
}
