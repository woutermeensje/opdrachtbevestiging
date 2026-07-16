<?php

namespace App\Http\Controllers;

use App\Mail\QuoteInvitationMail;
use App\Models\Confirmation;
use App\Models\Quote;
use App\Models\User;
use App\Services\QuotePdfService;
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

class QuoteController extends Controller
{
    public function __construct(
        private readonly QuotePdfService $pdfService,
    ) {}

    public function index(Request $request): View
    {
        $quotes = $request->user()
            ->quotes()
            ->latest()
            ->get();

        return view('dashboard.quotes', [
            'quotes' => $quotes,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.quote-create', [
            'contacts' => auth()->user()->contacts()->orderBy('company_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'contact_id' => ['required', 'integer'],
            'description' => ['required', 'string'],
            'valid_until' => ['nullable', 'date'],
            'total_value' => ['nullable', 'numeric', 'min:0'],
            'value_vat_type' => ['nullable', 'in:excl,incl'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'],
            'submit_action' => ['nullable', 'string', 'in:send,test'],
        ], [], [
            'attachment' => 'bijlage',
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

        $quote = $request->user()->quotes()->create([
            'contact_id' => $contact->id,
            'reference' => $this->generateReference(),
            'title' => $validated['title'],
            'client_name' => $contact->company_name,
            'client_contact_name' => $contact->contactName(),
            'client_email' => $contact->contact_email,
            'client_kvk_number' => $contact->kvk_number,
            'description' => $description,
            'valid_until' => $validated['valid_until'] ?? null,
            'total_value' => $validated['total_value'] ?? 0,
            'value_vat_type' => $validated['value_vat_type'] ?? 'excl',
            'status' => 'concept',
            'sender_name' => trim((string) $request->user()->first_name.' '.(string) $request->user()->last_name),
            'sender_email' => $request->user()->email,
        ] + $this->profileSnapshotAttributes($request->user()));

        $this->copyProfileLogoToQuote($quote);

        $uploadedAttachment = $this->storeUploadedDocument($request->file('attachment'), $quote);

        if ($uploadedAttachment !== []) {
            $quote->forceFill($uploadedAttachment)->save();
        }

        try {
            $this->pdfService->generate($quote);

            if (($validated['submit_action'] ?? 'send') === 'test') {
                $this->sendTestQuoteEmail($quote);

                return redirect()
                    ->route('dashboard.quotes.show', $quote)
                    ->with('status', 'Testmail is verzonden naar '.$request->user()->email.'. De offerte staat nog als concept.');
            }

            $this->sendQuoteEmail($quote);
        } catch (Throwable $exception) {
            return redirect()
                ->route('dashboard.quotes.show', $quote)
                ->with('status', 'Offerte opgeslagen, maar verzenden mislukt: '.$exception->getMessage());
        }

        $quote->forceFill([
            'status' => 'verzonden',
            'sent_at' => now(),
        ])->save();

        return redirect()
            ->route('dashboard.quotes.show', $quote)
            ->with('status', 'Offerte is per e-mail verzonden naar '.$quote->client_email.'.');
    }

    public function show(Request $request, Quote $quote): View
    {
        abort_unless($quote->user_id === $request->user()->id, 403);

        return view('dashboard.quote-show', [
            'quote' => $quote,
        ]);
    }

    public function downloadPdf(Request $request, Quote $quote): StreamedResponse
    {
        abort_unless($quote->user_id === $request->user()->id, 403);
        abort_unless($quote->hasPdf(), 404);

        return Storage::disk('local')->download(
            $quote->pdf_path,
            $quote->pdf_original_name ?: $quote->pdfDownloadName(),
            ['Content-Type' => $quote->pdf_mime_type ?: 'application/pdf'],
        );
    }

    public function send(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($quote->user_id === $request->user()->id, 403);

        try {
            $this->pdfService->generate($quote);
            $this->sendQuoteEmail($quote);
        } catch (Throwable $exception) {
            return redirect()
                ->route('dashboard.quotes.show', $quote)
                ->with('status', 'Verzenden mislukt: '.$exception->getMessage());
        }

        $quote->forceFill([
            'status' => 'verzonden',
            'sent_at' => now(),
        ])->save();

        return redirect()
            ->route('dashboard.quotes.show', $quote)
            ->with('status', 'Offerte is per e-mail verzonden naar '.$quote->client_email.'.');
    }

    public function markAccepted(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($quote->user_id === $request->user()->id, 403);

        $quote->forceFill(['status' => 'geaccepteerd'])->save();

        return redirect()
            ->route('dashboard.quotes.show', $quote)
            ->with('status', 'Offerte is gemarkeerd als geaccepteerd.');
    }

    public function markRejected(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($quote->user_id === $request->user()->id, 403);

        $quote->forceFill(['status' => 'afgewezen'])->save();

        return redirect()
            ->route('dashboard.quotes.show', $quote)
            ->with('status', 'Offerte is gemarkeerd als afgewezen.');
    }

    private function sendQuoteEmail(Quote $quote): void
    {
        Mail::to($quote->client_email)
            ->cc($quote->user->email)
            ->send(new QuoteInvitationMail($quote));
    }

    private function sendTestQuoteEmail(Quote $quote): void
    {
        Mail::to($quote->user->email)
            ->send(new QuoteInvitationMail($quote));
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
        ];
    }

    private function copyProfileLogoToQuote(Quote $quote): void
    {
        $user = $quote->user;

        if (filled($quote->sender_company_logo_path) || ! filled($user->company_logo_path) || ! Storage::disk('local')->exists($user->company_logo_path)) {
            return;
        }

        $extension = pathinfo($user->company_logo_original_name ?: $user->company_logo_path, PATHINFO_EXTENSION) ?: 'bin';
        $targetPath = 'quotes/'.$quote->id.'/bedrijfslogo/bedrijfslogo.'.$extension;

        if (! Storage::disk('local')->copy($user->company_logo_path, $targetPath)) {
            throw new RuntimeException('Het profielbestand kon niet aan de offerte worden toegevoegd.');
        }

        $quote->forceFill([
            'sender_company_logo_path' => $targetPath,
            'sender_company_logo_original_name' => $user->company_logo_original_name,
            'sender_company_logo_mime_type' => $user->company_logo_mime_type,
        ])->save();
    }

    /**
     * @return array<string, string|null>
     */
    private function storeUploadedDocument(mixed $file, Quote $quote): array
    {
        if (! $file instanceof UploadedFile) {
            return [];
        }

        $path = $file->store('quotes/'.$quote->id.'/bijlagen', 'local');

        if ($path === false) {
            throw new RuntimeException('Het uploadbestand kon niet worden opgeslagen.');
        }

        return [
            'attachment_path' => $path,
            'attachment_original_name' => $file->getClientOriginalName(),
            'attachment_mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
        ];
    }

    private function generateReference(): string
    {
        do {
            $reference = 'OF-'.Str::upper(Str::random(8));
        } while (Quote::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
