<?php

namespace App\Http\Middleware;

use App\Models\Confirmation;
use App\Models\User;
use App\Services\ActivityLogger;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Legt geslaagde acties van ingelogde gebruikers vast op basis van de
 * routes in config/activity.php.
 */
class LogUserActivity
{
    public function __construct(private ActivityLogger $logger) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Gebruiker vooraf vastleggen: na bijvoorbeeld het verwijderen van een
        // account is er geen ingelogde gebruiker meer.
        $user = $request->user();

        $response = $next($request);

        $routeName = $request->route()?->getName();
        // Geen dot-notatie: routenamen bevatten zelf punten.
        $description = $routeName ? (config('activity.actions')[$routeName] ?? null) : null;

        if ($description === null || ! $this->succeeded($request, $response)) {
            return $response;
        }

        $subject = $this->subject($request, $user);
        $user ??= $subject instanceof Confirmation ? $subject->user : null;

        if ($user instanceof User) {
            $this->logger->log($user, $routeName, $description, $subject);
        }

        return $response;
    }

    private function succeeded(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        // Validatiefouten resulteren in een redirect met geflashte 'errors'.
        return ! ($request->hasSession() && in_array('errors', $request->session()->get('_flash.new', []), true));
    }

    private function subject(Request $request, ?User $user): ?Model
    {
        foreach ($request->route()?->parameters() ?? [] as $name => $parameter) {
            if ($parameter instanceof Model) {
                return $parameter;
            }

            // Niet alle controllers gebruiken route model binding (bijv. int $contact).
            if ($user && $name === 'contact' && is_numeric($parameter)) {
                return $user->contacts()->find($parameter);
            }
        }

        if ($request->route()?->hasParameter('token')) {
            return Confirmation::query()->where('public_token', $request->route('token'))->first();
        }

        return null;
    }
}
