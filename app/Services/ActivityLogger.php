<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class ActivityLogger
{
    /**
     * Legt een activiteit vast. Fouten worden gerapporteerd maar breken nooit
     * de eigenlijke actie van de gebruiker.
     *
     * @param  array<string, mixed>  $properties
     */
    public function log(?User $user, string $action, string $description, ?Model $subject = null, array $properties = []): ?ActivityLog
    {
        try {
            $request = request();

            return ActivityLog::create([
                'user_id' => $user?->exists ? $user->getKey() : null,
                'user_name' => $user?->name,
                'user_email' => $user?->email ?? ($properties['email'] ?? null),
                'action' => $action,
                'description' => $description,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'properties' => $properties ?: null,
                'ip_address' => $request->ip(),
                'user_agent' => str((string) $request->userAgent())->limit(1000)->value() ?: null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
