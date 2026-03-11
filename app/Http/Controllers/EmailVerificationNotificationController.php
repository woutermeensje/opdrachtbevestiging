<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Er is een nieuwe verificatiemail verzonden.');
    }
}
