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
        'reference',
        'title',
        'client_name',
        'client_email',
        'description',
        'total_value',
        'public_token',
        'status',
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

    public function publicUrl(): string
    {
        return URL::route('confirmations.public.show', $this->public_token);
    }
}
