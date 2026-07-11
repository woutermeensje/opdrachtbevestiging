<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmationSignedMail;
use App\Models\Confirmation;
use App\Services\ConfirmationPdfService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PublicConfirmationController extends Controller
{
    public function __construct(
        private readonly ConfirmationPdfService $pdfService,
    ) {}

    public function show(string $token): View|RedirectResponse
    {
        $confirmation = Confirmation::query()
            ->where('public_token', $token)
            ->firstOrFail();

        if ($confirmation->status === 'getekend') {
            return redirect()->route('confirmations.public.signed', $token);
        }

        if ($confirmation->viewed_at === null) {
            $confirmation->forceFill([
                'viewed_at' => now(),
            ])->save();
        }

        return view('confirmations.public-show', [
            'confirmation' => $confirmation,
        ]);
    }

    public function signed(string $token): View|RedirectResponse
    {
        $confirmation = Confirmation::query()
            ->where('public_token', $token)
            ->firstOrFail();

        if ($confirmation->status !== 'getekend') {
            return redirect()->route('confirmations.public.show', $token);
        }

        return view('confirmations.public-signed', [
            'confirmation' => $confirmation,
        ]);
    }

    public function downloadPdf(string $token): StreamedResponse
    {
        $confirmation = Confirmation::query()
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless($confirmation->hasPdf(), 404);

        return Storage::disk('local')->download(
            $confirmation->pdf_path,
            $confirmation->pdf_original_name ?: $confirmation->pdfDownloadName(),
            ['Content-Type' => $confirmation->pdf_mime_type ?: 'application/pdf'],
        );
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $confirmation = Confirmation::query()
            ->where('public_token', $token)
            ->firstOrFail();

        abort_if($confirmation->status !== 'verzonden' || $confirmation->signed_at !== null, 409);

        $validated = $request->validate([
            'signer_name' => ['required', 'string', 'max:255'],
            'accept_terms' => ['accepted'],
            'signer_signature_data' => ['required', 'string'],
        ]);

        $signaturePath = $this->storeSignatureImage($confirmation, $validated['signer_signature_data']);

        $confirmation->forceFill([
            'status' => 'getekend',
            'signed_at' => now(),
            'signer_name' => $validated['signer_name'],
            'signer_signature_path' => $signaturePath,
            'signer_signature_mime_type' => 'image/png',
            'signer_ip' => $request->ip(),
            'signer_user_agent' => str((string) $request->userAgent())->limit(1000)->value(),
        ])->save();

        $this->pdfService->generate($confirmation);

        $this->sendSignedConfirmationEmails($confirmation);

        return redirect()
            ->route('confirmations.public.signed', $confirmation->public_token)
            ->with('status', 'Het akkoord op de opdrachtbevestiging is vastgelegd.');
    }

    private function sendSignedConfirmationEmails(Confirmation $confirmation): void
    {
        try {
            Mail::to($confirmation->client_email)
                ->send(new ConfirmationSignedMail($confirmation, forClient: true));

            Mail::to($confirmation->user->email)
                ->send(new ConfirmationSignedMail($confirmation, forClient: false));
        } catch (Throwable $exception) {
            Log::error('Verzenden van de getekende opdrachtbevestiging is mislukt.', [
                'confirmation_id' => $confirmation->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function storeSignatureImage(Confirmation $confirmation, string $signatureData): string
    {
        if (! preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/=]+)$/', $signatureData, $matches)) {
            throw ValidationException::withMessages([
                'signer_signature_data' => 'Zet je handtekening in het handtekeningvak.',
            ]);
        }

        $signature = base64_decode($matches[1], true);

        if ($signature === false || strlen($signature) < 50) {
            throw ValidationException::withMessages([
                'signer_signature_data' => 'Zet je handtekening in het handtekeningvak.',
            ]);
        }

        $path = 'confirmations/'.$confirmation->id.'/handtekeningen/handtekening.png';

        if (! Storage::disk('local')->put($path, $signature)) {
            throw ValidationException::withMessages([
                'signer_signature_data' => 'De handtekening kon niet worden opgeslagen. Probeer het opnieuw.',
            ]);
        }

        return $path;
    }
}
