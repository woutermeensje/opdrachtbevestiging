<?php

namespace App\Services;

use App\Models\Confirmation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ConfirmationPdfService
{
    public function __construct(
        private readonly LogoResizer $logoResizer,
    ) {}

    public function generate(Confirmation $confirmation): void
    {
        $confirmation->loadMissing('user');
        $this->logoResizer->shrinkStored($confirmation->sender_company_logo_path);

        $path = 'confirmations/'.$confirmation->id.'/pdf/'.$confirmation->pdfDownloadName();
        $contents = Pdf::loadView('pdf.confirmation', [
            'confirmation' => $confirmation,
        ])->setPaper('a4')->output();

        if (! Storage::disk('local')->put($path, $contents)) {
            throw new RuntimeException('De PDF kon niet worden opgeslagen.');
        }

        $confirmation->forceFill([
            'pdf_path' => $path,
            'pdf_original_name' => $confirmation->pdfDownloadName(),
            'pdf_mime_type' => 'application/pdf',
            'pdf_generated_at' => now(),
        ])->save();
    }
}
