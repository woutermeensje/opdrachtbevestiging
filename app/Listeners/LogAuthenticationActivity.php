<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

class LogAuthenticationActivity
{
    public function __construct(private ActivityLogger $logger) {}

    public function handle(Login|Logout|Failed|Registered|Verified|PasswordReset $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;

        match (true) {
            $event instanceof Login => $this->logger->log($user, 'auth.login', $event->remember ? 'Ingelogd (onthouden)' : 'Ingelogd'),
            $event instanceof Logout => $this->logger->log($user, 'auth.logout', 'Uitgelogd'),
            $event instanceof Failed => $this->logger->log($user, 'auth.failed', 'Mislukte inlogpoging', properties: [
                'email' => $event->credentials['email'] ?? null,
            ]),
            $event instanceof Registered => $this->logger->log($user, 'auth.registered', 'Account geregistreerd'),
            $event instanceof Verified => $this->logger->log($user, 'auth.verified', 'E-mailadres bevestigd'),
            $event instanceof PasswordReset => $this->logger->log($user, 'auth.password_reset', 'Wachtwoord opnieuw ingesteld via e-mail'),
        };
    }
}
