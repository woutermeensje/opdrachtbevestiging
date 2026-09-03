<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use RuntimeException;

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
        View::share('themePrimaryColor', $this->themePrimaryColor());

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

    private function themePrimaryColor(): string
    {
        static $color = null;

        if ($color !== null) {
            return $color;
        }

        foreach ($this->themeStylesheetPaths() as $path) {
            $css = @file_get_contents($path);

            if (is_string($css) && preg_match('/--color-primary:\s*(#[0-9A-Fa-f]{6})\s*;/', $css, $matches)) {
                return $color = strtoupper($matches[1]);
            }
        }

        throw new RuntimeException('Unable to resolve --color-primary from the application stylesheet.');
    }

    /**
     * @return array<int, string>
     */
    private function themeStylesheetPaths(): array
    {
        $paths = [resource_path('css/app.css')];
        $manifestPath = public_path('build/manifest.json');

        if (! is_file($manifestPath)) {
            return $paths;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        if (! is_array($manifest)) {
            return $paths;
        }

        $cssFiles = $manifest['resources/css/app.css']['css'] ?? [];

        foreach ($cssFiles as $cssFile) {
            $paths[] = public_path('build/'.$cssFile);
        }

        return $paths;
    }
}
