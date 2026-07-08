<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmationInvitationMail;
use App\Models\Confirmation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
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
            'description' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'],
            'quote' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'],
        ], [], [
            'attachment' => 'bijlage',
            'quote' => 'offerte',
        ]);

        $description = Confirmation::sanitizeDescription($validated['description']);

        if ($description === null) {
            return back()
                ->withInput()
                ->withErrors(['description' => 'Vul het tekstblok in.']);
        }

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
            'description' => $description,
            'total_value' => 0,
            'public_token' => Str::random(40),
            'status' => 'concept',
            'sender_name' => trim((string) $request->user()->first_name.' '.(string) $request->user()->last_name),
            'sender_email' => $request->user()->email,
        ]);

        $uploadedFiles = array_filter([
            'attachment' => $this->storeUploadedDocument($request->file('attachment'), $confirmation, 'bijlagen', 'attachment'),
            'quote' => $this->storeUploadedDocument($request->file('quote'), $confirmation, 'offertes', 'quote'),
        ]);

        if ($uploadedFiles !== []) {
            $confirmation->forceFill(collect($uploadedFiles)->collapse()->all())->save();
        }

        try {
            $this->sendConfirmationEmail($confirmation);
        } catch (Throwable $exception) {
            return redirect()
                ->route('dashboard.confirmations.show', $confirmation)
                ->with('status', 'Opdrachtbevestiging opgeslagen, maar e-mailverzending mislukt: '.$exception->getMessage());
        }

        $confirmation->forceFill([
            'status' => 'verzonden',
            'sent_at' => now(),
        ])->save();

        return redirect()
            ->route('dashboard.confirmations.show', $confirmation)
            ->with('status', 'Opdrachtbevestiging is per e-mail verzonden naar '.$confirmation->client_email.'.');
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
            $this->sendConfirmationEmail($confirmation);
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

    private function sendConfirmationEmail(Confirmation $confirmation): void
    {
        Mail::to($confirmation->client_email)->send(new ConfirmationInvitationMail($confirmation));
    }

    /**
     * @return array<string, string|null>
     */
    private function storeUploadedDocument(mixed $file, Confirmation $confirmation, string $directory, string $fieldPrefix): array
    {
        if (! $file instanceof UploadedFile) {
            return [];
        }

        $path = $file->store('confirmations/'.$confirmation->id.'/'.$directory, 'local');

        if ($path === false) {
            throw new RuntimeException('Het uploadbestand kon niet worden opgeslagen.');
        }

        return [
            $fieldPrefix.'_path' => $path,
            $fieldPrefix.'_original_name' => $file->getClientOriginalName(),
            $fieldPrefix.'_mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
        ];
    }

    private function generateReference(): string
    {
        do {
            $reference = 'OB-'.Str::upper(Str::random(8));
        } while (Confirmation::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
