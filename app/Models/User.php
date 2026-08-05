<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'phone_number',
        'company_name',
        'kvk_number',
        'street_name',
        'house_number',
        'house_number_addition',
        'postal_code',
        'city',
        'country',
        'company_logo_path',
        'company_logo_original_name',
        'company_logo_mime_type',
        'primary_color',
        'secondary_color',
        'terms_path',
        'terms_original_name',
        'terms_mime_type',
        'default_agreements',
        'default_specifications',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'default_specifications' => 'array',
            'password' => 'hashed',
        ];
    }

    public function confirmations(): HasMany
    {
        return $this->hasMany(Confirmation::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function hasCompletedCompanyProfile(): bool
    {
        return filled($this->company_name) && filled($this->kvk_number);
    }

    /**
     * @return array<int, string>
     */
    public function companyAddressLines(): array
    {
        $streetLine = trim(implode(' ', array_filter([
            $this->street_name,
            $this->house_number,
            $this->house_number_addition,
        ])));

        $cityLine = trim(implode(' ', array_filter([
            $this->postal_code,
            $this->city,
        ])));

        return array_values(array_filter([
            $streetLine,
            $cityLine,
            $this->country,
        ]));
    }

    public function defaultAgreementsHtml(): string
    {
        return Confirmation::sanitizeDescription($this->default_agreements) ?? '';
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function normalizedDefaultSpecifications(): array
    {
        return Confirmation::normalizeSpecifications($this->default_specifications ?? []);
    }
}
