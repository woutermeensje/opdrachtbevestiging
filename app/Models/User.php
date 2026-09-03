<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

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
        'trial_ends_at',
        'subscription_status',
        'subscription_plan',
        'subscription_started_at',
        'subscription_renews_at',
        'mollie_customer_id',
        'mollie_mandate_id',
        'mollie_subscription_id',
        'mollie_pending_payment_id',
        'pending_subscription_plan',
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
            'trial_ends_at' => 'datetime',
            'subscription_started_at' => 'datetime',
            'subscription_renews_at' => 'datetime',
            'default_specifications' => 'array',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
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

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active';
    }

    public function hasPendingSubscription(): bool
    {
        return $this->subscription_status === 'pending';
    }

    public function isOnTrial(): bool
    {
        return ! $this->hasActiveSubscription()
            && $this->trial_ends_at instanceof Carbon
            && $this->trial_ends_at->isFuture();
    }

    public function hasExpiredTrial(): bool
    {
        return ! $this->hasActiveSubscription()
            && $this->trial_ends_at instanceof Carbon
            && $this->trial_ends_at->isPast();
    }

    public function hasBillingAccess(): bool
    {
        return $this->hasActiveSubscription() || $this->isOnTrial();
    }

    public function trialDaysRemaining(): int
    {
        if (! $this->trial_ends_at instanceof Carbon || ! $this->trial_ends_at->isFuture()) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInSeconds($this->trial_ends_at, false) / 86400));
    }

    public function subscriptionPlanName(): ?string
    {
        $planName = config('billing.plans.'.($this->subscription_plan ?? $this->pending_subscription_plan).'.name');

        return is_string($planName) ? $planName : null;
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
