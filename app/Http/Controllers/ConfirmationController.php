<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmationInvitationMail;
use App\Models\Confirmation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

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
        return view('dashboard.create', [
            'contacts' => auth()->user()->contacts()->orderBy('company_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'contact_id' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
            'total_value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:concept,verzonden,getekend,wacht-op-akkoord'],
            'agreement_date' => ['nullable', 'date'],
            'sent_at' => ['nullable', 'date'],
            'signed_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $contact = $request->user()
            ->contacts()
            ->findOrFail($validated['contact_id']);

        $confirmation = $request->user()->confirmations()->create([
            'contact_id' => $contact->id,
            'reference' => $this->generateReference(),
            'title' => $validated['title'],
            'client_name' => $contact->company_name,
            'client_contact_name' => $contact->contactName(),
            'client_email' => $contact->contact_email,
            'client_kvk_number' => $contact->kvk_number,
            'description' => $validated['description'] ?? null,
            'total_value' => $validated['total_value'],
            'public_token' => Str::random(40),
            'status' => $validated['status'],
            'sender_name' => trim((string) $request->user()->first_name.' '.(string) $request->user()->last_name),
            'sender_email' => $request->user()->email,
            'agreement_date' => $validated['agreement_date'] ?? null,
            'sent_at' => $validated['sent_at'] ?? null,
            'signed_at' => $validated['signed_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
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

        try {
            Mail::to($confirmation->client_email)->send(new ConfirmationInvitationMail($confirmation));
        } catch (Throwable $exception) {
            return redirect()
                ->route('dashboard.confirmations.show', $confirmation)
                ->with('status', 'E-mailverzending mislukt: '.$exception->getMessage());
        }

        $confirmation->forceFill([
            'status' => 'verzonden',
            'sent_at' => now(),
        ])->save();

        return redirect()
            ->route('dashboard.confirmations.show', $confirmation)
            ->with('status', 'Opdrachtbevestiging is per e-mail verzonden naar '.$confirmation->client_email.'.');
    }

    private function generateReference(): string
    {
        do {
            $reference = 'OB-'.Str::upper(Str::random(8));
        } while (Confirmation::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
