<?php

namespace Tests\Feature;

use App\Models\Confirmation;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SignhostSendFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Storage::fake('local');
    }

    public function test_authenticated_user_can_send_confirmation_via_signhost(): void
    {
        Http::fake([
            'https://api.signhost.com/api/transaction' => Http::response([
                'Id' => 'tx-123',
                'Status' => 5,
            ], 201),
            'https://api.signhost.com/api/transaction/tx-123/file/*' => Http::response([], 200),
            'https://api.signhost.com/api/transaction/tx-123/start' => Http::response([], 200),
        ]);

        $user = User::factory()->create([
            'first_name' => 'Wouter',
            'last_name' => 'Meens',
            'company_name' => 'Studio Wouter',
            'email' => 'wouter@example.test',
        ]);

        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Acme B.V.',
            'contact_first_name' => 'Sanne',
            'contact_last_name' => 'Jansen',
            'contact_email' => 'sanne@acme.test',
        ]);

        $confirmation = Confirmation::factory()->create([
            'user_id' => $user->id,
            'contact_id' => $contact->id,
            'client_name' => $contact->company_name,
            'client_contact_name' => $contact->contactName(),
            'client_email' => $contact->contact_email,
            'sender_name' => 'Wouter Meens',
            'sender_email' => 'wouter@example.test',
            'status' => 'concept',
            'signhost_status' => 'draft',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.confirmations.send', $confirmation));

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));

        $confirmation->refresh();

        $this->assertSame('tx-123', $confirmation->signhost_transaction_id);
        $this->assertSame('waiting_for_signer', $confirmation->signhost_status);
        $this->assertSame('verzonden', $confirmation->status);

        Http::assertSentCount(4);
        Storage::disk('local')->assertExists(
            'confirmations/'.$confirmation->id.'/'.Str::slug($confirmation->reference).'.pdf'
        );
    }

    public function test_webhook_marks_confirmation_as_signed_and_stores_documents(): void
    {
        Http::fake([
            'https://api.signhost.com/api/transaction/tx-123/file/*' => Http::response('%PDF signed', 200, ['Content-Type' => 'application/pdf']),
            'https://api.signhost.com/api/file/receipt/tx-123' => Http::response('%PDF receipt', 200, ['Content-Type' => 'application/pdf']),
        ]);

        $confirmation = Confirmation::factory()->create([
            'signhost_transaction_id' => 'tx-123',
            'signhost_file_id' => 'confirmation-OB-1234.pdf',
            'status' => 'verzonden',
            'signhost_status' => 'waiting_for_signer',
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer test-webhook-token')
            ->postJson(route('signhost.webhook'), [
                'Id' => 'tx-123',
                'Status' => 30,
            ]);

        $response->assertOk();

        $confirmation->refresh();

        $this->assertSame('getekend', $confirmation->status);
        $this->assertSame('signed', $confirmation->signhost_status);
        $this->assertNotNull($confirmation->signhost_signed_document_path);
        $this->assertNotNull($confirmation->signhost_receipt_path);

        Storage::disk('local')->assertExists($confirmation->signhost_signed_document_path);
        Storage::disk('local')->assertExists($confirmation->signhost_receipt_path);
    }
}
