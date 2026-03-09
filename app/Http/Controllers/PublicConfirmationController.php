<?php

namespace App\Http\Controllers;

use App\Models\Confirmation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicConfirmationController extends Controller
{
    public function show(string $token): View
    {
        $confirmation = Confirmation::query()
            ->where('public_token', $token)
            ->firstOrFail();

        if ($confirmation->viewed_at === null) {
            $confirmation->forceFill([
                'viewed_at' => now(),
            ])->save();
        }

        return view('confirmations.public-show', [
            'confirmation' => $confirmation,
        ]);
    }

    public function sign(Request $request, string $token): RedirectResponse
    {
        $confirmation = Confirmation::query()
            ->where('public_token', $token)
            ->firstOrFail();

        abort_if($confirmation->status === 'getekend', 409);

        $validated = $request->validate([
            'signer_name' => ['required', 'string', 'max:255'],
            'accept_terms' => ['accepted'],
        ]);

        $confirmation->forceFill([
            'status' => 'getekend',
            'signed_at' => now(),
            'signer_name' => $validated['signer_name'],
            'signer_ip' => $request->ip(),
            'signer_user_agent' => str((string) $request->userAgent())->limit(1000)->value(),
        ])->save();

        return redirect()
            ->route('confirmations.public.show', $confirmation->public_token)
            ->with('status', 'De opdrachtbevestiging is ondertekend.');
    }
}
