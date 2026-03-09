<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmationInvitationMail;
use App\Models\Confirmation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ConfirmationController extends Controller
{
    public function index(Request $request): View
    {
        $confirmations = $request->user()
            ->confirmations()
            ->latest()
            ->get();

        return view('dashboard.confirmations', [
            'confirmations' => $confirmations,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'description' => ['nullable', 'string'],
            'total_value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:concept,verzonden,getekend,wacht-op-akkoord'],
            'agreement_date' => ['nullable', 'date'],
            'sent_at' => ['nullable', 'date'],
            'signed_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $confirmation = $request->user()->confirmations()->create([
            ...$validated,
            'reference' => $this->generateReference(),
            'public_token' => Str::random(40),
        ]);

        return redirect()
            ->route('dashboard.confirmations.show', $confirmation)
            ->with('status', 'Opdrachtbevestiging opgeslagen.');
    }

    public function show(Request $request, Confirmation $confirmation): View
    {
        abort_unless($confirmation->user_id === $request->user()->id, 403);

        return view('dashboard.confirmation-show', [
            'confirmation' => $confirmation,
        ]);
    }

    public function send(Request $request, Confirmation $confirmation): RedirectResponse
    {
        abort_unless($confirmation->user_id === $request->user()->id, 403);

        if ($confirmation->public_token === null) {
            $confirmation->forceFill([
                'public_token' => Str::random(40),
            ])->save();
        }

        Mail::to($confirmation->client_email)->send(
            new ConfirmationInvitationMail($confirmation)
        );

        $confirmation->forceFill([
            'status' => 'verzonden',
            'sent_at' => now(),
        ])->save();

        return redirect()
            ->route('dashboard.confirmations.show', $confirmation)
            ->with('status', 'Opdrachtbevestiging is verzonden naar '.$confirmation->client_email.'.');
    }

    private function generateReference(): string
    {
        do {
            $reference = 'OB-'.Str::upper(Str::random(8));
        } while (Confirmation::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
