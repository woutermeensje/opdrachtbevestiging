<?php

namespace Tests\Feature;

use App\Mail\ConfirmationInvitationMail;
use App\Mail\ConfirmationRetractionMail;
use App\Models\Confirmation;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConfirmationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->withoutMiddleware(EnsureEmailIsVerified::class);
    }

    public function test_authenticated_user_can_create_confirmation(): void
    {
        Mail::fake();
        Storage::fake('local');

        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Acme B.V.',
            'contact_first_name' => 'Sanne',
            'contact_last_name' => 'Jansen',
            'contact_email' => 'info@acme.test',
            'kvk_number' => '12345678',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.create.store'), [
                'title' => 'Nieuwe website opdracht',
                'contact_id' => $contact->id,
                'description' => '<p>Ontwikkeling van een <strong>marketingwebsite</strong>.</p>',
            ]);

        $confirmation = Confirmation::query()->first();

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));
        $this->assertNotNull($confirmation);
        $this->assertSame($user->id, $confirmation->user_id);
        $this->assertSame('Acme B.V.', $confirmation->client_name);
        $this->assertSame('Sanne Jansen', $confirmation->client_contact_name);
        $this->assertSame('info@acme.test', $confirmation->client_email);
        $this->assertSame('verzonden', $confirmation->status);
        $this->assertSame('0.00', $confirmation->total_value);
        $this->assertNotNull($confirmation->sent_at);
        $this->assertNotNull($confirmation->pdf_path);
        $this->assertSame('<p>Ontwikkeling van een <strong>marketingwebsite</strong>.</p>', $confirmation->description);

        Storage::disk('local')->assertExists($confirmation->pdf_path);

        $this->actingAs($user)
            ->get(route('dashboard.confirmations.pdf', $confirmation))
            ->assertOk()
            ->assertDownload($confirmation->pdf_original_name);

        Mail::assertSent(ConfirmationInvitationMail::class, function (ConfirmationInvitationMail $mail) use ($confirmation): bool {
            $mail->assertSeeInHtml('<strong>marketingwebsite</strong>', false);
            $mail->assertSeeInHtml('Akkoord geven');
            $mail->assertSeeInHtml($confirmation->publicUrl());
            $mail->assertHasAttachment(
                Attachment::fromStorageDisk('local', $confirmation->pdf_path)
                    ->as($confirmation->pdf_original_name)
                    ->withMime('application/pdf')
            );

            return $mail->hasTo($confirmation->client_email);
        });
    }

    public function test_create_confirmation_form_uses_simplified_fields(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Acme B.V.',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.create'));

        $response
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="title"', false)
            ->assertSee('data-quill-editor', false)
            ->assertSee('name="contact_id"', false)
            ->assertSee('name="attachment"', false)
            ->assertSee('name="quote"', false)
            ->assertSee('Verzenden')
            ->assertSee('Verzend test')
            ->assertSee('Vaste gegevens')
            ->assertSee('name="agreement_date"', false)
            ->assertSee('name="duration"', false)
            ->assertSee('name="total_value"', false)
            ->assertSee('name="value_vat_type"', false)
            ->assertSee('name="termination_terms"', false)
            ->assertDontSee('name="status"', false);
    }

    public function test_confirmation_captures_scope_details_and_shows_them_in_email(): void
    {
        Mail::fake();
        Storage::fake('local');

        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Acme B.V.',
            'contact_email' => 'info@acme.test',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.create.store'), [
                'title' => 'Nieuwe website opdracht',
                'contact_id' => $contact->id,
                'description' => '<p>Ontwikkeling van een website.</p>',
                'agreement_date' => '2026-08-01',
                'duration' => '3 maanden',
                'total_value' => '1250.50',
                'value_vat_type' => 'incl',
                'termination_terms' => 'Beide partijen kunnen schriftelijk opzeggen met een termijn van 1 maand.',
            ]);

        $confirmation = Confirmation::query()->first();

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));
        $this->assertSame('2026-08-01', $confirmation->agreement_date->toDateString());
        $this->assertSame('3 maanden', $confirmation->duration);
        $this->assertSame('1250.50', $confirmation->total_value);
        $this->assertSame('incl', $confirmation->value_vat_type);
        $this->assertSame('Beide partijen kunnen schriftelijk opzeggen met een termijn van 1 maand.', $confirmation->termination_terms);

        Mail::assertSent(ConfirmationInvitationMail::class, function (ConfirmationInvitationMail $mail) use ($confirmation): bool {
            $mail->assertSeeInHtml('3 maanden');
            $mail->assertSeeInHtml('incl. BTW');
            $mail->assertSeeInHtml('Beide partijen kunnen schriftelijk opzeggen');

            return $mail->hasTo($confirmation->client_email);
        });
    }

    public function test_authenticated_user_can_send_test_confirmation_to_self(): void
    {
        Mail::fake();
        Storage::fake('local');

        $user = User::factory()->create([
            'email' => 'wouter@example.test',
        ]);
        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Acme B.V.',
            'contact_first_name' => 'Sanne',
            'contact_last_name' => 'Jansen',
            'contact_email' => 'sanne@acme.test',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.create.store'), [
                'title' => 'Nieuwe website opdracht',
                'contact_id' => $contact->id,
                'description' => '<p>Ontwikkeling van een website.</p>',
                'submit_action' => 'test',
            ]);

        $confirmation = Confirmation::query()->first();

        $response
            ->assertRedirect(route('dashboard.confirmations.show', $confirmation))
            ->assertSessionHas('status');

        $this->assertSame('concept', $confirmation->status);
        $this->assertNull($confirmation->sent_at);
        $this->assertNotNull($confirmation->pdf_path);

        Storage::disk('local')->assertExists($confirmation->pdf_path);

        Mail::assertSent(ConfirmationInvitationMail::class, function (ConfirmationInvitationMail $mail) use ($confirmation, $user): bool {
            $mail->assertSeeInHtml('Ontwikkeling van een website.');
            $mail->assertHasAttachment(
                Attachment::fromStorageDisk('local', $confirmation->pdf_path)
                    ->as($confirmation->pdf_original_name)
                    ->withMime('application/pdf')
            );

            return $mail->hasTo($user->email) && ! $mail->hasTo($confirmation->client_email);
        });
    }

    public function test_authenticated_user_can_create_confirmation_with_attachment_and_quote(): void
    {
        Mail::fake();
        Storage::fake('local');

        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Acme B.V.',
            'contact_email' => 'info@acme.test',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.create.store'), [
                'title' => 'Nieuwe website opdracht',
                'contact_id' => $contact->id,
                'description' => '<p>Ontwikkeling van een website.</p>',
                'attachment' => UploadedFile::fake()->create('voorwaarden.pdf', 32, 'application/pdf'),
                'quote' => UploadedFile::fake()->create('offerte.pdf', 48, 'application/pdf'),
            ]);

        $confirmation = Confirmation::query()->first();

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));
        $this->assertNotNull($confirmation);
        $this->assertNotNull($confirmation->attachment_path);
        $this->assertNotNull($confirmation->quote_path);
        $this->assertSame('voorwaarden.pdf', $confirmation->attachment_original_name);
        $this->assertSame('offerte.pdf', $confirmation->quote_original_name);

        Storage::disk('local')->assertExists($confirmation->attachment_path);
        Storage::disk('local')->assertExists($confirmation->quote_path);
        Storage::disk('local')->assertExists($confirmation->pdf_path);

        Mail::assertSent(ConfirmationInvitationMail::class, function (ConfirmationInvitationMail $mail) use ($confirmation): bool {
            $mail->assertHasAttachment(
                Attachment::fromStorageDisk('local', $confirmation->pdf_path)
                    ->as($confirmation->pdf_original_name)
                    ->withMime('application/pdf')
            );
            $mail->assertHasAttachment(
                Attachment::fromStorageDisk('local', $confirmation->attachment_path)
                    ->as('voorwaarden.pdf')
                    ->withMime('application/pdf')
            );
            $mail->assertHasAttachment(
                Attachment::fromStorageDisk('local', $confirmation->quote_path)
                    ->as('offerte.pdf')
                    ->withMime('application/pdf')
            );
            $mail->assertSeeInHtml('Bijlage: voorwaarden.pdf');
            $mail->assertSeeInHtml('Offerte: offerte.pdf');

            return $mail->hasTo($confirmation->client_email);
        });
    }

    public function test_profile_defaults_are_added_to_created_confirmation_and_email(): void
    {
        Mail::fake();
        Storage::fake('local');

        $user = User::factory()->create([
            'company_name' => 'Studio Wouter',
            'kvk_number' => '12345678',
            'street_name' => 'Keizersgracht',
            'house_number' => '1',
            'postal_code' => '1015CJ',
            'city' => 'Amsterdam',
            'country' => 'Nederland',
            'default_agreements' => '<p>Betaling binnen <strong>14 dagen</strong>.</p>',
        ]);

        Storage::disk('local')->put('profiles/'.$user->id.'/algemene-voorwaarden/voorwaarden.pdf', 'voorwaarden');
        $user->forceFill([
            'terms_path' => 'profiles/'.$user->id.'/algemene-voorwaarden/voorwaarden.pdf',
            'terms_original_name' => 'algemene-voorwaarden.pdf',
            'terms_mime_type' => 'application/pdf',
        ])->save();

        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Acme B.V.',
            'contact_email' => 'info@acme.test',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.create.store'), [
                'title' => 'Nieuwe website opdracht',
                'contact_id' => $contact->id,
                'description' => '<p>Ontwikkeling van een website.</p>',
            ]);

        $confirmation = Confirmation::query()->first();

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));
        $this->assertSame('Studio Wouter', $confirmation->sender_company_name);
        $this->assertSame('12345678', $confirmation->sender_kvk_number);
        $this->assertSame('<p>Betaling binnen <strong>14 dagen</strong>.</p>', $confirmation->default_agreements);
        $this->assertNotNull($confirmation->terms_path);
        $this->assertNotSame($user->terms_path, $confirmation->terms_path);

        Storage::disk('local')->assertExists($confirmation->terms_path);
        Storage::disk('local')->assertExists($confirmation->pdf_path);

        Mail::assertSent(ConfirmationInvitationMail::class, function (ConfirmationInvitationMail $mail) use ($confirmation): bool {
            $mail->assertSeeInHtml('Betaling binnen <strong>14 dagen</strong>', false);
            $mail->assertHasAttachment(
                Attachment::fromStorageDisk('local', $confirmation->terms_path)
                    ->as('algemene-voorwaarden.pdf')
                    ->withMime('application/pdf')
            );
            $mail->assertHasAttachment(
                Attachment::fromStorageDisk('local', $confirmation->pdf_path)
                    ->as($confirmation->pdf_original_name)
                    ->withMime('application/pdf')
            );

            return $mail->hasTo($confirmation->client_email);
        });
    }

    public function test_confirmations_page_shows_only_own_confirmations(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownConfirmation = Confirmation::factory()->create([
            'user_id' => $user->id,
            'client_name' => 'Eigen klant',
        ]);

        Confirmation::factory()->create([
            'user_id' => $otherUser->id,
            'client_name' => 'Andere klant',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.confirmations'));

        $response
            ->assertOk()
            ->assertSee($ownConfirmation->client_name)
            ->assertDontSee('Andere klant');
    }

    public function test_user_cannot_view_another_users_confirmation(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $confirmation = Confirmation::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard.confirmations.show', $confirmation));

        $response->assertForbidden();
    }

    public function test_user_can_send_confirmation_email(): void
    {
        Mail::fake();
        Http::fake();
        Storage::fake('local');

        $user = User::factory()->create([
            'first_name' => 'Wouter',
            'last_name' => 'Meens',
            'company_name' => 'Studio Wouter',
            'email' => 'wouter@example.test',
        ]);
        $confirmation = $user->confirmations()->create([
            'reference' => 'OB-SENDTEST',
            'title' => 'Nieuwe website opdracht',
            'client_name' => 'Acme B.V.',
            'client_contact_name' => 'Sanne Jansen',
            'client_email' => 'sanne@acme.test',
            'description' => 'Ontwikkeling van een marketingwebsite.',
            'total_value' => '1000.00',
            'public_token' => 'send-test-token',
            'status' => 'concept',
            'sender_name' => 'Wouter Meens',
            'sender_email' => 'wouter@example.test',
            'agreement_date' => '2026-03-08',
            'sent_at' => null,
            'expires_at' => '2026-03-23',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.confirmations.send', $confirmation));

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));

        $confirmation->refresh();

        $this->assertSame('verzonden', $confirmation->status);
        $this->assertNotNull($confirmation->sent_at);
        $this->assertNotNull($confirmation->pdf_path);
        $this->assertSame('Studio Wouter', $confirmation->sender_company_name);
        $this->assertNull($confirmation->signhost_transaction_id);

        Storage::disk('local')->assertExists($confirmation->pdf_path);

        Mail::assertSent(ConfirmationInvitationMail::class, function (ConfirmationInvitationMail $mail) use ($confirmation): bool {
            $mail->assertHasSubject('Opdrachtbevestiging van Studio Wouter');
            $mail->assertSeeInHtml('Deze e-mail bevat de volledige opdrachtbevestiging');
            $mail->assertSeeInHtml('Akkoord geven');
            $mail->assertSeeInHtml('Ontwikkeling van een marketingwebsite.');
            $mail->assertSeeInText($confirmation->publicUrl());
            $mail->assertSeeInText('Ontwikkeling van een marketingwebsite.');
            $mail->assertHasAttachment(
                Attachment::fromStorageDisk('local', $confirmation->pdf_path)
                    ->as($confirmation->pdf_original_name)
                    ->withMime('application/pdf')
            );

            return $mail->hasTo($confirmation->client_email);
        });

        Http::assertNothingSent();
    }

    public function test_user_can_retract_sent_confirmation(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'first_name' => 'Wouter',
            'last_name' => 'Meens',
            'company_name' => 'Studio Wouter',
            'email' => 'wouter@example.test',
        ]);
        $confirmation = Confirmation::factory()->create([
            'user_id' => $user->id,
            'reference' => 'OB-RETRACT',
            'title' => 'Nieuwe website opdracht',
            'client_name' => 'Acme B.V.',
            'client_contact_name' => 'Sanne Jansen',
            'client_email' => 'sanne@acme.test',
            'status' => 'verzonden',
            'signed_at' => null,
            'sender_name' => 'Wouter Meens',
            'sender_email' => 'wouter@example.test',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.confirmations.retract', $confirmation));

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));

        $confirmation->refresh();

        $this->assertSame('ingetrokken', $confirmation->status);
        $this->assertNull($confirmation->signed_at);

        Mail::assertSent(ConfirmationRetractionMail::class, function (ConfirmationRetractionMail $mail) use ($confirmation): bool {
            $mail->assertHasSubject('Opdrachtbevestiging OB-RETRACT is ingetrokken');
            $mail->assertSeeInHtml('is ingetrokken');
            $mail->assertSeeInText('is ingetrokken');

            return $mail->hasTo($confirmation->client_email);
        });
    }

    public function test_signed_confirmation_cannot_be_retracted(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $confirmation = Confirmation::factory()->create([
            'user_id' => $user->id,
            'status' => 'getekend',
            'signed_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.confirmations.retract', $confirmation));

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));
        $this->assertSame('getekend', $confirmation->fresh()->status);

        Mail::assertNothingSent();
    }

    public function test_public_recipient_can_accept_confirmation(): void
    {
        Storage::fake('local');

        $confirmation = Confirmation::factory()->create([
            'status' => 'verzonden',
            'signed_at' => null,
        ]);

        $response = $this->post(route('confirmations.public.accept', $confirmation->public_token), [
            'signer_name' => 'Jan de Vries',
            'accept_terms' => '1',
            'signer_signature_data' => $this->signatureDataUri(),
        ]);

        $response->assertRedirect(route('confirmations.public.show', $confirmation->public_token));

        $confirmation->refresh();

        $this->assertSame('getekend', $confirmation->status);
        $this->assertSame('Jan de Vries', $confirmation->signer_name);
        $this->assertNotNull($confirmation->signed_at);
        $this->assertNotNull($confirmation->signer_signature_path);
        $this->assertNotNull($confirmation->pdf_path);

        Storage::disk('local')->assertExists($confirmation->signer_signature_path);
        Storage::disk('local')->assertExists($confirmation->pdf_path);
    }

    public function test_public_recipient_must_draw_signature_to_accept_confirmation(): void
    {
        $confirmation = Confirmation::factory()->create([
            'status' => 'verzonden',
            'signed_at' => null,
        ]);

        $response = $this->from(route('confirmations.public.show', $confirmation->public_token))
            ->post(route('confirmations.public.accept', $confirmation->public_token), [
                'signer_name' => 'Jan de Vries',
                'accept_terms' => '1',
            ]);

        $response
            ->assertRedirect(route('confirmations.public.show', $confirmation->public_token))
            ->assertSessionHasErrors('signer_signature_data');

        $confirmation->refresh();

        $this->assertSame('verzonden', $confirmation->status);
        $this->assertNull($confirmation->signed_at);
        $this->assertNull($confirmation->signer_signature_path);
    }

    public function test_public_recipient_cannot_accept_retracted_confirmation(): void
    {
        $confirmation = Confirmation::factory()->create([
            'status' => 'ingetrokken',
            'signed_at' => null,
        ]);

        $this->get(route('confirmations.public.show', $confirmation->public_token))
            ->assertOk()
            ->assertSee('Deze opdrachtbevestiging is ingetrokken')
            ->assertDontSee('type="submit" class="btn btn-primary">Akkoord bevestigen</button>', false);

        $response = $this->post(route('confirmations.public.accept', $confirmation->public_token), [
            'signer_name' => 'Jan de Vries',
            'accept_terms' => '1',
        ]);

        $response->assertStatus(409);
        $this->assertSame('ingetrokken', $confirmation->fresh()->status);
    }

    public function test_authenticated_user_can_store_a_contact(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.contacts.store'), [
                'company_name' => 'Voorbeeld B.V.',
                'street_name' => 'Keizersgracht',
                'house_number' => '1',
                'postal_code' => '1015CJ',
                'city' => 'Amsterdam',
                'contact_first_name' => 'Piet',
                'contact_last_name' => 'de Boer',
                'contact_email' => 'piet@example.test',
                'contact_phone' => '0612345678',
            ]);

        $response->assertRedirect(route('dashboard.contacts'));

        $this->assertDatabaseHas('contacts', [
            'user_id' => $user->id,
            'company_name' => 'Voorbeeld B.V.',
            'contact_email' => 'piet@example.test',
        ]);
    }

    private function signatureDataUri(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';
    }
}
