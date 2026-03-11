<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'kvk_number',
        'street_name',
        'house_number',
        'house_number_addition',
        'postal_code',
        'city',
        'country',
        'contact_first_name',
        'contact_last_name',
        'contact_email',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmations(): HasMany
    {
        return $this->hasMany(Confirmation::class);
    }

    public function contactName(): string
    {
        return trim($this->contact_first_name.' '.$this->contact_last_name);
    }
}
