<?php

namespace App\Services;

use App\Models\Confirmation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConfirmationPdfService
{
    public function render(Confirmation $confirmation): string
    {
        return Pdf::loadView('confirmations.pdf', [
            'confirmation' => $confirmation,
        ])->output();
    }

    public function store(Confirmation $confirmation): string
    {
        $path = 'confirmations/'.$confirmation->id.'/'.Str::slug($confirmation->reference).'.pdf';

        Storage::disk('local')->put($path, $this->render($confirmation));

        return $path;
    }
}
