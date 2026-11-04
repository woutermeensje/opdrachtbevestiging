<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(config('activity.retention_days', 365)));
    }

    /**
     * Groep van de actie (auth, confirmation, quote, ...) voor badges en filters.
     */
    public function group(): string
    {
        return str($this->action)->before('.')->value();
    }

    public function subjectLabel(): ?string
    {
        return match (true) {
            $this->subject instanceof Confirmation, $this->subject instanceof Quote => $this->subject->reference ?: $this->subject->title,
            $this->subject instanceof Contact => $this->subject->company_name
                ?: trim($this->subject->contact_first_name.' '.$this->subject->contact_last_name),
            default => null,
        };
    }

    public function tone(): string
    {
        return match ($this->action) {
            'auth.failed' => 'danger',
            'auth.login', 'auth.registered', 'auth.verified' => 'success',
            'profile.account.destroy' => 'danger',
            default => 'muted',
        };
    }
}
