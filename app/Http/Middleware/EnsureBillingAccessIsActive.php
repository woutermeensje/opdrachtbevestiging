<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBillingAccessIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasBillingAccess()) {
            return redirect()
                ->route('billing.show')
                ->with('status', 'Je gratis proefperiode is afgelopen. Kies een abonnement om verder te gaan.');
        }

        return $next($request);
    }
}
