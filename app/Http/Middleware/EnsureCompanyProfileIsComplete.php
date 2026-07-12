<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyProfileIsComplete
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasCompletedCompanyProfile()) {
            return redirect()
                ->route('dashboard.profile.company')
                ->with('status', 'Vul eerst je bedrijfsgegevens aan. Pas daarna kun je opdrachtbevestigingen versturen.');
        }

        return $next($request);
    }
}
