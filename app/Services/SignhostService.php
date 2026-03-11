<?php

namespace App\Services;

use App\Models\Confirmation;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SignhostService
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly ConfirmationPdfService $pdfService,
    ) {
    }

    public function sendConfirmation(Confirmation $confirmation): array
    {
        $fileId = $this->fileIdFor($confirmation);
        $transaction = $this->createTransaction($confirmation);
        $transactionId = (string) ($transaction['Id'] ?? '');

        if ($transactionId === '') {
            throw new RuntimeException('Signhost gaf geen transaction ID terug.');
        }

        $pdfPath = $this->pdfService->store($confirmation);
        $pdfBinary = Storage::disk('local')->get($pdfPath);

        $this->uploadFileMetadata($transactionId, $fileId, $confirmation);
        $this->uploadFile($transactionId, $fileId, $pdfBinary);
        $this->startTransaction($transactionId);

        return [
            'transaction_id' => $transactionId,
            'file_id' => $fileId,
            'pdf_path' => $pdfPath,
        ];
    }

    public function synchronize(Confirmation $confirmation): array
    {
        $transactionId = (string) $confirmation->signhost_transaction_id;

        if ($transactionId === '') {
            throw new RuntimeException('Deze opdrachtbevestiging heeft nog geen Signhost transaction ID.');
        }

        $transaction = $this->transaction($transactionId);
        $status = (int) ($transaction['Status'] ?? 0);

        $attributes = [
            'signhost_status' => $this->mapStatus($status),
        ];

        if ($status === 30) {
            $attributes['status'] = 'getekend';
            $attributes['signed_at'] = now();
            $attributes['signhost_completed_at'] = now();
            $attributes['signhost_signed_document_path'] = $this->storeSignedDocument($confirmation);
            $attributes['signhost_receipt_path'] = $this->storeReceipt($confirmation);
        }

        if ($status === 50) {
            $attributes['status'] = 'wacht-op-akkoord';
        }

        $confirmation->forceFill($attributes)->save();

        return $transaction;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handlePostback(array $payload): void
    {
        $transactionId = (string) ($payload['Id'] ?? '');

        if ($transactionId === '') {
            return;
        }

        $confirmation = Confirmation::query()
            ->where('signhost_transaction_id', $transactionId)
            ->first();

        if (! $confirmation) {
            return;
        }

        $status = (int) ($payload['Status'] ?? 0);

        $attributes = [
            'signhost_status' => $this->mapStatus($status),
        ];

        if ($status === 30) {
            $attributes['status'] = 'getekend';
            $attributes['signed_at'] = $confirmation->signed_at ?? now();
            $attributes['signhost_completed_at'] = now();
            $attributes['signhost_signed_document_path'] = $this->storeSignedDocument($confirmation);
            $attributes['signhost_receipt_path'] = $this->storeReceipt($confirmation);
        }

        $confirmation->forceFill($attributes)->save();
    }

    /**
     * @return array<string, mixed>
     */
    public function transaction(string $transactionId): array
    {
        return $this->request()
            ->get('/transaction/'.$transactionId)
            ->throw()
            ->json();
    }

    public function signedDocumentResponse(Confirmation $confirmation): Response
    {
        return $this->request()
            ->get('/transaction/'.$confirmation->signhost_transaction_id.'/file/'.$this->fileIdFor($confirmation))
            ->throw();
    }

    public function receiptResponse(Confirmation $confirmation): Response
    {
        return $this->request()
            ->get('/file/receipt/'.$confirmation->signhost_transaction_id)
            ->throw();
    }

    /**
     * @return array<string, mixed>
     */
    private function createTransaction(Confirmation $confirmation): array
    {
        return $this->request()
            ->post('/transaction', [
                'PostbackUrl' => config('services.signhost.postback_url'),
                'Language' => 'nl-NL',
                'SendEmailNotifications' => true,
                'Signers' => [
                    [
                        'Email' => $confirmation->sender_email,
                        'RequireScribble' => true,
                        'SendSignRequest' => true,
                        'SignRequestMessage' => 'Onderteken eerst deze opdrachtbevestiging namens '.$confirmation->user->company_name.'.',
                        'DaysToRemind' => 7,
                        'ScribbleName' => $confirmation->sender_name,
                        'ScribbleNameFixed' => false,
                    ],
                    [
                        'Email' => $confirmation->client_email,
                        'RequireScribble' => true,
                        'SendSignRequest' => true,
                        'SignRequestMessage' => 'Onderteken deze opdrachtbevestiging namens '.$confirmation->client_name.'.',
                        'DaysToRemind' => 7,
                        'ScribbleName' => $confirmation->client_contact_name ?: $confirmation->client_name,
                        'ScribbleNameFixed' => false,
                    ],
                ],
            ])
            ->throw()
            ->json();
    }

    private function uploadFileMetadata(string $transactionId, string $fileId, Confirmation $confirmation): void
    {
        $this->request()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->put('/transaction/'.$transactionId.'/file/'.$fileId, [
                'DisplayName' => $confirmation->title,
                'Signers' => [
                    'Signer1' => new \stdClass(),
                    'Signer2' => new \stdClass(),
                ],
            ])
            ->throw();
    }

    private function uploadFile(string $transactionId, string $fileId, string $pdfBinary): void
    {
        $this->request()
            ->withHeaders(['Content-Type' => 'application/pdf'])
            ->withBody($pdfBinary, 'application/pdf')
            ->put('/transaction/'.$transactionId.'/file/'.$fileId)
            ->throw();
    }

    private function startTransaction(string $transactionId): void
    {
        $this->request()
            ->put('/transaction/'.$transactionId.'/start')
            ->throw();
    }

    private function storeSignedDocument(Confirmation $confirmation): string
    {
        $path = 'confirmations/'.$confirmation->id.'/signed-'.$this->fileIdFor($confirmation).'.pdf';

        Storage::disk('local')->put($path, $this->signedDocumentResponse($confirmation)->body());

        return $path;
    }

    private function storeReceipt(Confirmation $confirmation): string
    {
        $path = 'confirmations/'.$confirmation->id.'/receipt-'.$this->fileIdFor($confirmation).'.pdf';

        Storage::disk('local')->put($path, $this->receiptResponse($confirmation)->body());

        return $path;
    }

    private function fileIdFor(Confirmation $confirmation): string
    {
        return $confirmation->signhost_file_id ?: 'confirmation-'.$confirmation->reference.'.pdf';
    }

    private function mapStatus(int $status): string
    {
        return match ($status) {
            5 => 'waiting_for_document',
            10 => 'waiting_for_signer',
            20 => 'in_progress',
            30 => 'signed',
            40 => 'rejected',
            50 => 'expired',
            60 => 'cancelled',
            70 => 'failed',
            default => 'unknown',
        };
    }

    private function request()
    {
        $applicationKey = (string) config('services.signhost.application_key');
        $userToken = (string) config('services.signhost.user_token');
        $baseUrl = rtrim((string) config('services.signhost.base_url'), '/');

        if ($applicationKey === '' || $userToken === '') {
            throw new RuntimeException('Signhost credentials ontbreken.');
        }

        return $this->http
            ->baseUrl($baseUrl)
            ->acceptJson()
            ->timeout(30)
            ->withHeaders([
                'Authorization' => 'APIKey '.$userToken,
                'Application' => 'APPKey '.$applicationKey,
            ]);
    }
}
