<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmationInvitationMail;
use App\Mail\ConfirmationRetractionMail;
use App\Models\Confirmation;
use App\Models\User;
use App\Services\ConfirmationPdfService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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

    public function create(Request $request): View
    {
        return view('dashboard.create', [
            'contacts' => $request->user()->contacts()->orderBy('company_name')->get(),
            'draft' => $this->resumableDraft($request->user()),
        ]);
    }

    /**
     * Slaat de aanmaakwizard automatisch op als concept. Wordt aangeroepen
     * vanuit de browser bij het verlaten van /dashboard/aanmaken zonder te
     * verzenden, en tussentijds terwijl er wordt getypt.
     */
    public function storeDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'draft_id' => ['nullable', 'integer'],
            'sender_role' => ['nullable', 'string', 'in:'.implode(',', Confirmation::senderRoleValues())],
            'title' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string', 'max:20000'],
            'footer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $draft = $this->findDraft($request->user(), $validated['draft_id'] ?? null);

        $title = trim((string) ($validated['title'] ?? ''));
        $senderRole = Confirmation::normalizeSenderRole($validated['sender_role'] ?? null);
        $description = Confirmation::sanitizeDescription($validated['description'] ?? null);
        $footerNote = Confirmation::sanitizeFooterNote($validated['footer_note'] ?? null);
        $defaultFooterNote = Confirmation::defaultFooterNoteForUser($request->user());

        $contact = filled($validated['contact_id'] ?? null)
            ? $request->user()->contacts()->find($validated['contact_id'])
            : null;

        $hasContent = $title !== ''
            || $senderRole !== Confirmation::DEFAULT_SENDER_ROLE
            || filled($description)
            || $contact !== null
            || (filled($footerNote) && $footerNote !== $defaultFooterNote);

        if (! $hasContent) {
            // Niets zinvols ingevuld: geen leeg concept aanmaken, wel een
            // bestaand concept opruimen als alles is leeggemaakt.
            $draft?->delete();

            return response()->json(['draft_id' => null]);
        }

        $attributes = [
            'contact_id' => $contact?->id,
            'title' => $title,
            'client_name' => $contact?->company_name ?? '',
            'client_contact_name' => $contact?->contactName(),
            'client_email' => $contact?->contact_email ?? '',
            'client_kvk_number' => $contact?->kvk_number,
            'client_street_name' => $contact?->street_name,
            'client_house_number' => $contact?->house_number,
            'client_house_number_addition' => $contact?->house_number_addition,
            'client_postal_code' => $contact?->postal_code,
            'client_city' => $contact?->city,
            'client_country' => $contact?->country,
            'description' => $description,
            'footer_note' => $footerNote,
            'status' => 'concept',
            'sender_role' => $senderRole,
            'is_draft' => true,
        ];

        if ($draft !== null) {
            $draft->forceFill($attributes)->save();
        } else {
            // Hooguit één automatisch concept per gebruiker.
            $request->user()->confirmations()->where('is_draft', true)->delete();

            $draft = $request->user()->confirmations()->create($attributes + [
                'reference' => $this->generateReference(),
                'public_token' => Str::random(40),
                'sender_name' => trim((string) $request->user()->first_name.' '.(string) $request->user()->last_name),
                'sender_email' => $request->user()->email,
            ]);
        }

        return response()->json(['draft_id' => $draft->id]);
    }

    /**
     * Verwijdert het automatisch opgeslagen concept ("opnieuw beginnen").
     */
    public function discardDraft(Request $request): RedirectResponse
    {
        $this->findDraft($request->user(), $request->integer('draft_id'))?->delete();

        return redirect()->route('dashboard.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'draft_id' => ['nullable', 'integer'],
            'sender_role' => ['nullable', 'string', 'in:'.implode(',', Confirmation::senderRoleValues())],
            'title' => ['required', 'string', 'max:255'],
            'contact_id' => ['required', 'integer'],
            'description' => ['required', 'string'],
            'footer_note' => ['nullable', 'string', 'max:2000'],
            'agreement_date' => ['nullable', 'date'],
            'duration' => ['nullable', 'string', 'max:255'],
            'total_value' => ['nullable', 'numeric', 'min:0'],
            'value_vat_type' => ['nullable', 'in:excl,incl'],
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

        $draft = $this->findDraft($request->user(), $validated['draft_id'] ?? null);

        $attributes = [
            'contact_id' => $contact->id,
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
            'value_vat_type' => $validated['value_vat_type'] ?? 'excl',
            'status' => 'concept',
            'sender_role' => Confirmation::normalizeSenderRole($validated['sender_role'] ?? null),
            'is_draft' => false,
            'sender_name' => trim((string) $request->user()->first_name.' '.(string) $request->user()->last_name),
            'sender_email' => $request->user()->email,
        ] + $this->profileSnapshotAttributes($request->user());

        if ($draft !== null) {
            $draft->forceFill($attributes)->save();
            $confirmation = $draft;
        } else {
            $confirmation = $request->user()->confirmations()->create($attributes + [
                'reference' => $this->generateReference(),
                'public_token' => Str::random(40),
            ]);
        }

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

    public function show(Request $request, Confirmation $confirmation): View|RedirectResponse
    {
        abort_unless($confirmation->user_id === $request->user()->id, 403);

        // Een automatisch opgeslagen concept heeft nog geen detailpagina:
        // daar ga je in de aanmaakwizard mee verder.
        if ($confirmation->is_draft) {
            return redirect()->route('dashboard.create');
        }

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

    public function previewPdf(Request $request, Confirmation $confirmation): StreamedResponse
    {
        abort_unless($confirmation->user_id === $request->user()->id, 403);
        abort_unless($confirmation->hasPdf(), 404);

        return Storage::disk('local')->response(
            $confirmation->pdf_path,
            $confirmation->pdf_original_name ?: $confirmation->pdfDownloadName(),
            ['Content-Type' => $confirmation->pdf_mime_type ?: 'application/pdf'],
        );
    }

    public function send(Request $request, Confirmation $confirmation): RedirectResponse
    {
        abort_unless($confirmation->user_id === $request->user()->id, 403);

        // Een automatisch opgeslagen concept wordt eerst in de wizard afgerond.
        if ($confirmation->is_draft) {
            return redirect()->route('dashboard.create');
        }

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
            ->with('status', 'Opdrachtbevestiging is ingetrokken. De relatie is per e-mail geinformeerd.');
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
            'sender_company_trade_name' => $user->company_trade_name,
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
     * Het meest recente automatisch opgeslagen concept van deze gebruiker dat
     * nog niet is verzonden, om de aanmaakwizard mee te herstellen.
     */
    private function resumableDraft(User $user): ?Confirmation
    {
        return $user->confirmations()
            ->where('is_draft', true)
            ->whereNull('sent_at')
            ->latest('updated_at')
            ->first();
    }

    private function findDraft(User $user, int|string|null $draftId): ?Confirmation
    {
        if (! filled($draftId)) {
            return null;
        }

        return $user->confirmations()
            ->where('is_draft', true)
            ->whereNull('sent_at')
            ->find($draftId);
    }
}
