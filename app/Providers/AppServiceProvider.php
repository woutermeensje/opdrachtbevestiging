<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Bevestig je e-mailadres')
                ->greeting('Welkom bij '.config('app.name'))
                ->line('Bevestig je e-mailadres om je account te activeren en toegang te krijgen tot je dashboard.')
                ->action('E-mailadres bevestigen', $url)
                ->line('Heb je je niet zelf geregistreerd? Dan hoef je niets te doen.');
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $url = URL::route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], true);

            return (new MailMessage)
                ->subject('Stel je wachtwoord opnieuw in')
                ->greeting('Wachtwoord opnieuw instellen')
                ->line('Je ontvangt deze e-mail omdat er een aanvraag is gedaan om het wachtwoord van je account opnieuw in te stellen.')
                ->action('Nieuw wachtwoord instellen', $url)
                ->line('Deze link verloopt na '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minuten.')
                ->line('Heb je dit niet aangevraagd? Dan kun je deze e-mail negeren.');
        });
    }
}
