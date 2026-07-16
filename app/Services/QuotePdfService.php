<?php

namespace App\Services;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class QuotePdfService
{
    public function generate(Quote $quote): void
    {
        $quote->loadMissing('user');

        $path = 'quotes/'.$quote->id.'/pdf/'.$quote->pdfDownloadName();
        $contents = Pdf::loadView('pdf.quote', [
            'quote' => $quote,
        ])->setPaper('a4')->output();

        if (! Storage::disk('local')->put($path, $contents)) {
            throw new RuntimeException('De PDF kon niet worden opgeslagen.');
        }

        $quote->forceFill([
            'pdf_path' => $path,
            'pdf_original_name' => $quote->pdfDownloadName(),
            'pdf_mime_type' => 'application/pdf',
            'pdf_generated_at' => now(),
        ])->save();
    }
}
