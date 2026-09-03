<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmationInvitationMail;
use App\Mail\ConfirmationRetractionMail;
use App\Models\Confirmation;
use App\Models\User;
use App\Services\ConfirmationPdfService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ConfirmationController extends Controller
{
    public function __construct(
        private readonly ConfirmationPdfService $pdfService,
    ) {}

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
        if ($request->filled('total_value')) {
            $request->merge([
                'total_value' => $this->normalizeAmount((string) $request->input('total_value')),
            ]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'contact_id' => ['required', 'integer'],
            'description' => ['required', 'string'],
            'footer_note' => ['nullable', 'string', 'max:2000'],
            'agreement_date' => ['nullable', 'date'],
            'duration' => ['nullable', 'string', 'max:255'],
            'total_value' => ['nullable', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'integer', 'in:0,9,21'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'],
            'quote' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'],
            'submit_action' => ['nullable', 'string', 'in:send,test'],
        ] + Confirmation::specificationValidationRules(), [], [
            'attachment' => 'bijlage',
            'quote' => 'offerte',
        ]);

        $description = Confirmation::sanitizeDescription($validated['description']);
        $footerNote = Confirmation::sanitizeFooterNote($validated['footer_note'] ?? null)
            ?? Confirmation::defaultFooterNoteForUser($request->user());

        if ($description === null) {
            return back()
                ->withInput()
                ->withErrors(['description' => 'Vul het tekstblok in.']);
        }

        $specifications = Confirmation::normalizeSpecifications(array_replace_recursive(
            $request->user()->normalizedDefaultSpecifications(),
            $validated['specifications'] ?? [],
        ));

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
            'client_street_name' => $contact->street_name,
            'client_house_number' => $contact->house_number,
            'client_house_number_addition' => $contact->house_number_addition,
            'client_postal_code' => $contact->postal_code,
            'client_city' => $contact->city,
            'client_country' => $contact->country,
            'description' => $description,
            'footer_note' => $footerNote,
            'specifications' => $specifications,
            'agreement_date' => $validated['agreement_date'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'total_value' => $validated['total_value'] ?? 0,
            'value_vat_type' => 'excl',
            'vat_rate' => $validated['vat_rate'] ?? 21,
            'public_token' => Str::random(40),
            'status' => 'concept',
            'sender_name' => trim((string) $request->user()->first_name.' '.(string) $request->user()->last_name),
            'sender_email' => $request->user()->email,
        ] + $this->profileSnapshotAttributes($request->user()));

        $this->copyProfileFilesToConfirmation($confirmation);

        $uploadedFiles = array_filter([
            'attachment' => $this->storeUploadedDocument($request->file('attachment'), $confirmation, 'bijlagen', 'attachment'),
            'quote' => $this->storeUploadedDocument($request->file('quote'), $confirmation, 'offertes', 'quote'),
        ]);

        if ($uploadedFiles !== []) {
            $confirmation->forceFill(collect($uploadedFiles)->collapse()->all())->save();
        }

        try {
            $this->pdfService->generate($confirmation);

            if (($validated['submit_action'] ?? 'send') === 'test') {
                $this->sendTestConfirmationEmail($confirmation);

                return redirect()
                    ->route('dashboard.confirmations.show', $confirmation)
                    ->with('status', 'Testmail is verzonden naar '.$request->user()->email.'. De opdrachtbevestiging staat nog als concept.');
            }

            $this->sendConfirmationEmail($confirmation);
        } catch (Throwable $exception) {
            return redirect()
                ->route('dashboard.confirmations.show', $confirmation)
                ->with('status', 'Opdrachtbevestiging opgeslagen, maar verzenden mislukt: '.$exception->getMessage());
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

    public function downloadPdf(Request $request, Confirmation $confirmation): StreamedResponse
    {
        abort_unless($confirmation->user_id === $request->user()->id, 403);
        abort_unless($confirmation->hasPdf(), 404);

        return Storage::disk('local')->download(
            $confirmation->pdf_path,
            $confirmation->pdf_original_name ?: $confirmation->pdfDownloadName(),
            ['Content-Type' => $confirmation->pdf_mime_type ?: 'application/pdf'],
        );
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
            $this->hydrateProfileSnapshot($confirmation);
            $this->pdfService->generate($confirmation);
            $this->sendConfirmationEmail($confirmation);
        } catch (Throwable $exception) {
            return redirect()
                ->route('dashboard.confirmations.show', $confirmation)
                ->with('status', 'Verzenden mislukt: '.$exception->getMessage());
        }

        $confirmation->forceFill([
            'status' => 'verzonden',
            'sent_at' => now(),
        ])->save();

        return redirect()
            ->route('dashboard.confirmations.show', $confirmation)
            ->with('status', 'Opdrachtbevestiging is per e-mail verzonden naar '.$confirmation->client_email.'.');
    }

    public function retract(Request $request, Confirmation $confirmation): RedirectResponse
    {
        abort_unless($confirmation->user_id === $request->user()->id, 403);

        if (! $confirmation->canBeRetracted()) {
            return redirect()
                ->route('dashboard.confirmations.show', $confirmation)
                ->with('status', 'Deze opdrachtbevestiging kan niet meer worden ingetrokken.');
        }

        try {
            Mail::to($confirmation->client_email)->send(new ConfirmationRetractionMail($confirmation));
        } catch (Throwable $exception) {
            return redirect()
                ->route('dashboard.confirmations.show', $confirmation)
                ->with('status', 'Intrekken mislukt, omdat de e-mail niet kon worden verzonden: '.$exception->getMessage());
        }

        $confirmation->forceFill([
            'status' => 'ingetrokken',
        ])->save();

        return redirect()
            ->route('dashboard.confirmations.show', $confirmation)
            ->with('status', 'Opdrachtbevestiging is ingetrokken. De opdrachtgever is per e-mail geinformeerd.');
    }

    private function sendConfirmationEmail(Confirmation $confirmation): void
    {
        Mail::to($confirmation->client_email)
            ->cc($confirmation->user->email)
            ->send(new ConfirmationInvitationMail($confirmation));
    }

    private function sendTestConfirmationEmail(Confirmation $confirmation): void
    {
        Mail::to($confirmation->user->email)
            ->send(new ConfirmationInvitationMail($confirmation));
    }

    /**
     * @return array<string, string|null>
     */
    private function profileSnapshotAttributes(User $user): array
    {
        return [
            'sender_company_name' => $user->company_name,
            'sender_kvk_number' => $user->kvk_number,
            'sender_street_name' => $user->street_name,
            'sender_house_number' => $user->house_number,
            'sender_house_number_addition' => $user->house_number_addition,
            'sender_postal_code' => $user->postal_code,
            'sender_city' => $user->city,
            'sender_country' => $user->country,
            'default_agreements' => Confirmation::sanitizeDescription($user->default_agreements),
        ];
    }

    private function hydrateProfileSnapshot(Confirmation $confirmation): void
    {
        $updates = [];

        foreach ($this->profileSnapshotAttributes($confirmation->user) as $field => $value) {
            if (! filled($confirmation->{$field}) && filled($value)) {
                $updates[$field] = $value;
            }
        }

        if ($updates !== []) {
            $confirmation->forceFill($updates)->save();
        }

        $this->copyProfileFilesToConfirmation($confirmation);
        $confirmation->refresh();
    }

    private function copyProfileFilesToConfirmation(Confirmation $confirmation): void
    {
        $user = $confirmation->user;

        $updates = array_merge(
            filled($confirmation->sender_company_logo_path) ? [] : $this->copyProfileFile(
                $user->company_logo_path,
                $confirmation,
                'bedrijfslogo',
                'bedrijfslogo',
                'sender_company_logo',
                $user->company_logo_original_name,
                $user->company_logo_mime_type,
            ),
            filled($confirmation->terms_path) ? [] : $this->copyProfileFile(
                $user->terms_path,
                $confirmation,
                'algemene-voorwaarden',
                'algemene-voorwaarden',
                'terms',
                $user->terms_original_name,
                $user->terms_mime_type,
            ),
        );

        if ($updates !== []) {
            $confirmation->forceFill($updates)->save();
        }
    }

    /**
     * @return array<string, string|null>
     */
    private function copyProfileFile(
        ?string $sourcePath,
        Confirmation $confirmation,
        string $directory,
        string $baseName,
        string $fieldPrefix,
        ?string $originalName,
        ?string $mimeType,
    ): array {
        if (! filled($sourcePath) || ! Storage::disk('local')->exists($sourcePath)) {
            return [];
        }

        $extension = pathinfo($originalName ?: $sourcePath, PATHINFO_EXTENSION) ?: 'bin';
        $targetPath = 'confirmations/'.$confirmation->id.'/'.$directory.'/'.$baseName.'.'.$extension;

        if (! Storage::disk('local')->copy($sourcePath, $targetPath)) {
            throw new RuntimeException('Het profielbestand kon niet aan de opdrachtbevestiging worden toegevoegd.');
        }

        return [
            $fieldPrefix.'_path' => $targetPath,
            $fieldPrefix.'_original_name' => $originalName,
            $fieldPrefix.'_mime_type' => $mimeType,
        ];
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

    /**
     * Zet een in het Nederlands ingevoerd bedrag ("5.000", "5.000,50",
     * "€ 1.234,56") om naar een machineleesbaar getal ("5000", "5000.50").
     * Punt = duizendtalscheiding, komma = decimaalteken.
     */
    private function normalizeAmount(string $value): string
    {
        $value = preg_replace('/[^0-9.,]/', '', $value) ?? '';

        if (str_contains($value, ',')) {
            // Nederlandse notatie: punt = duizendtal, komma = decimaal.
            return str_replace(['.', ','], ['', '.'], $value);
        }

        $dotCount = substr_count($value, '.');

        if ($dotCount === 0) {
            return $value;
        }

        if ($dotCount > 1) {
            // Meerdere punten kunnen alleen duizendtalscheidingen zijn.
            return str_replace('.', '', $value);
        }

        // Eén punt: exact 3 cijfers erachter lezen we als duizendtal ("5.000"),
        // anders als decimaalteken ("1250.50").
        [$whole, $rest] = explode('.', $value, 2);

        return strlen($rest) === 3 ? $whole.$rest : $value;
    }
}
