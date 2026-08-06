<?php

namespace Tests\Feature;

use App\Mail\ConfirmationInvitationMail;
use App\Mail\ConfirmationRetractionMail;
use App\Mail\ConfirmationSignedMail;
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
            $mail->assertSeeInHtml('Hoi Sanne,');
            $mail->assertSeeInText('Hoi Sanne,');
            $mail->assertSeeInHtml('accorderen');
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
            'contact_first_name' => 'Sanne',
            'contact_last_name' => 'Jansen',
            'contact_email' => 'sanne@acme.test',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.create'));

        $response
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="title"', false)
            ->assertSee('data-quill-editor', false)
            ->assertSee('name="contact_id"', false)
            ->assertSee('data-contact-search', false)
            ->assertSee('data-contact-search-option', false)
            ->assertSee('Acme B.V.')
            ->assertSee('sanne@acme.test')
            ->assertSee('Algemene gegevens')
            ->assertSee('Financiële afspraken')
            ->assertSee('Reiskosten')
            ->assertSee('Materialen')
            ->assertDontSee('Werkzaamheden')
            ->assertDontSee('Urenregistratie')
            ->assertDontSee('Juridisch')
            ->assertDontSee('Bijlagen')
            ->assertSee('name="specifications[general][client_reference]"', false)
            ->assertSee('name="specifications[planning][start_date]"', false)
            ->assertSee('name="specifications[planning][expected_duration]"', false)
            ->assertSee('name="specifications[financial][rate_unit]"', false)
            ->assertSee('name="specifications[financial][total_amount]"', false)
            ->assertDontSee('name="specifications[work][acceptance_criteria]"', false)
            ->assertDontSee('name="specifications[time_tracking][required]"', false)
            ->assertDontSee('name="specifications[legal][nda]"', false)
            ->assertDontSee('name="specifications[contact][client_phone]"', false)
            ->assertDontSee('name="specifications[attachments][nda_document]"', false)
            ->assertSee('name="attachment"', false)
            ->assertSee('name="quote"', false)
            ->assertSee('Verzenden')
            ->assertSee('Verzend test')
            ->assertSee('Vaste gegevens')
            ->assertDontSee('name="agreement_date"', false)
            ->assertDontSee('name="duration"', false)
            ->assertDontSee('name="total_value"', false)
            ->assertDontSee('name="value_vat_type"', false)
            ->assertSee('name="termination_terms"', false)
            ->assertDontSee('name="status"', false);
    }

    public function test_confirmation_stores_and_displays_optional_specifications(): void
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
                'specifications' => [
                    'general' => [
                        'client_reference' => 'INK-2026-42',
                        'project_name' => 'Website vernieuwing',
                    ],
                    'planning' => [
                        'end_date' => '2026-09-30',
                        'extension_possible' => 'yes',
                    ],
                    'financial' => [
                        'rate_unit' => 'per_hour',
                        'additional_work_allowed' => 'no',
                    ],
                    'materials' => [
                        'software_licenses' => "CMS-licentie door opdrachtgever.\nFigma-toegang wordt verstrekt.",
                    ],
                ],
                'submit_action' => 'test',
            ]);

        $confirmation = Confirmation::query()->firstOrFail();

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));
        $this->assertSame('INK-2026-42', $confirmation->specifications['general']['client_reference']);
        $this->assertSame('Website vernieuwing', $confirmation->specifications['general']['project_name']);
        $this->assertSame('yes', $confirmation->specifications['planning']['extension_possible']);
        $this->assertArrayNotHasKey('work', $confirmation->specifications);
        $this->assertArrayNotHasKey('time_tracking', $confirmation->specifications);
        $this->assertArrayNotHasKey('legal', $confirmation->specifications);
        $this->assertArrayNotHasKey('contact', $confirmation->specifications);
        $this->assertArrayNotHasKey('attachments', $confirmation->specifications);

        $filledSections = $confirmation->filledSpecificationSections();

        $this->assertSame('Algemene gegevens', $filledSections[0]['label']);
        $this->assertSame('Referentie opdrachtgever', $filledSections[0]['fields'][0]['label']);
        $this->assertSame('INK-2026-42', $filledSections[0]['fields'][0]['value']);
        $this->assertSame('Per uur', $filledSections[2]['fields'][0]['value']);
        $this->assertSame('Nee', $filledSections[2]['fields'][1]['value']);

        $this
            ->actingAs($user)
            ->get(route('dashboard.confirmations.show', $confirmation))
            ->assertOk()
            ->assertSee('Aanvullende specificaties')
            ->assertSee('Website vernieuwing')
            ->assertSee('Per uur')
            ->assertSee('CMS-licentie door opdrachtgever.');

        $this
            ->get(route('confirmations.public.show', $confirmation->public_token))
            ->assertOk()
            ->assertSee('Aanvullende specificaties')
            ->assertSee('Referentie opdrachtgever')
            ->assertSee('INK-2026-42')
            ->assertSee('Figma-toegang wordt verstrekt.', false);
    }

    public function test_create_confirmation_form_prefills_default_specifications(): void
    {
        $user = User::factory()->create([
            'default_specifications' => [
                'travel_expenses' => [
                    'compensation' => 'yes',
                    'mileage_compensation' => '0,23 per kilometer',
                ],
            ],
        ]);

        Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Acme B.V.',
            'contact_email' => 'info@acme.test',
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard.create'))
            ->assertOk()
            ->assertSee('name="specifications[travel_expenses][compensation]"', false)
            ->assertSee('value="yes" selected', false)
            ->assertSee('value="0,23 per kilometer"', false);
    }

    public function test_confirmation_uses_default_specifications_when_not_submitted(): void
    {
        Mail::fake();
        Storage::fake('local');

        $user = User::factory()->create([
            'default_specifications' => [
                'travel_expenses' => [
                    'compensation' => 'yes',
                    'mileage_compensation' => '0,23 per kilometer',
                ],
                'materials' => [
                    'laptop_provided' => 'no',
                ],
            ],
        ]);
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
                'submit_action' => 'test',
            ]);

        $confirmation = Confirmation::query()->firstOrFail();

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));
        $this->assertSame('yes', $confirmation->specifications['travel_expenses']['compensation']);
        $this->assertSame('0,23 per kilometer', $confirmation->specifications['travel_expenses']['mileage_compensation']);
        $this->assertSame('no', $confirmation->specifications['materials']['laptop_provided']);
        $this->assertArrayNotHasKey('legal', $confirmation->specifications);
    }

    public function test_confirmation_captures_scope_details(): void
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
            $mail->assertHasAttachment(
                Attachment::fromStorageDisk('local', $confirmation->pdf_path)
                    ->as($confirmation->pdf_original_name)
                    ->withMime('application/pdf')
            );

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
            ->assertDontSee('Opdrachtdatum')
            ->assertDontSee($ownConfirmation->client_contact_name ?: $ownConfirmation->client_email)
            ->assertDontSee('Andere klant');
    }

    public function test_dashboard_uses_same_confirmation_overview_content(): void
    {
        $user = User::factory()->create();
        $confirmation = Confirmation::factory()->create([
            'user_id' => $user->id,
            'reference' => 'OB-2026-001',
            'client_name' => 'Eigen klant',
            'client_contact_name' => 'Sanne Jansen',
            'client_email' => 'sanne@example.test',
            'agreement_date' => '2026-08-01',
            'sent_at' => '2026-08-02 10:00:00',
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Opdrachtbevestigingen')
            ->assertSee('Referentie')
            ->assertSee('Opdrachtgever')
            ->assertSee('Verzenddatum')
            ->assertSee('PDF')
            ->assertSee('OB-2026-001')
            ->assertSee('Eigen klant')
            ->assertSee('02-08-2026')
            ->assertDontSee('Opdrachtdatum')
            ->assertDontSee('Sanne Jansen')
            ->assertDontSee('01-08-2026')
            ->assertSee(route('dashboard.confirmations.show', $confirmation), false);
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
            $mail->assertDontSeeInHtml($confirmation->title);
            $mail->assertDontSeeInText($confirmation->title);
            $mail->assertSeeInHtml('Hoi Sanne,');
            $mail->assertSeeInText('Hoi Sanne,');
            $mail->assertSeeInHtml('heeft een opdrachtbevestiging opgesteld');
            $mail->assertSeeInHtml('accorderen');
            $mail->assertSeeInText($confirmation->publicUrl());
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

    public function test_public_acceptance_checkbox_renders_terms_text_without_blade_syntax(): void
    {
        $confirmationWithTerms = Confirmation::factory()->create([
            'status' => 'verzonden',
            'signed_at' => null,
            'terms_path' => 'confirmations/1/algemene-voorwaarden/voorwaarden.pdf',
        ]);

        $this->get(route('confirmations.public.show', $confirmationWithTerms->public_token))
            ->assertOk()
            ->assertSee('Ik ga akkoord met de inhoud van deze opdrachtbevestiging en de bijgevoegde algemene voorwaarden.')
            ->assertDontSee('@if')
            ->assertDontSee('$confirmation');

        $confirmationWithoutTerms = Confirmation::factory()->create([
            'status' => 'verzonden',
            'signed_at' => null,
            'terms_path' => null,
        ]);

        $this->get(route('confirmations.public.show', $confirmationWithoutTerms->public_token))
            ->assertOk()
            ->assertSee('Ik ga akkoord met de inhoud van deze opdrachtbevestiging.')
            ->assertDontSee('bijgevoegde algemene voorwaarden')
            ->assertDontSee('@if')
            ->assertDontSee('$confirmation');
    }

    public function test_public_recipient_can_accept_confirmation(): void
    {
        Storage::fake('local');
        Mail::fake();

        $confirmation = Confirmation::factory()->create([
            'status' => 'verzonden',
            'signed_at' => null,
        ]);

        $response = $this->post(route('confirmations.public.accept', $confirmation->public_token), [
            'signer_name' => 'Jan de Vries',
            'accept_terms' => '1',
            'signer_signature_data' => $this->signatureDataUri(),
        ]);

        $response->assertRedirect(route('confirmations.public.signed', $confirmation->public_token));

        $confirmation->refresh();

        $this->assertSame('getekend', $confirmation->status);
        $this->assertSame('Jan de Vries', $confirmation->signer_name);
        $this->assertNotNull($confirmation->signed_at);
        $this->assertNotNull($confirmation->signer_signature_path);
        $this->assertNotNull($confirmation->pdf_path);

        Storage::disk('local')->assertExists($confirmation->signer_signature_path);
        Storage::disk('local')->assertExists($confirmation->pdf_path);

        Mail::assertSent(ConfirmationSignedMail::class, function (ConfirmationSignedMail $mail) use ($confirmation): bool {
            return $mail->confirmation->is($confirmation) && $mail->forClient === true && $mail->hasTo($confirmation->client_email);
        });

        Mail::assertSent(ConfirmationSignedMail::class, function (ConfirmationSignedMail $mail) use ($confirmation): bool {
            return $mail->confirmation->is($confirmation) && $mail->forClient === false && $mail->hasTo($confirmation->user->email);
        });

        $this->get(route('confirmations.public.show', $confirmation->public_token))
            ->assertRedirect(route('confirmations.public.signed', $confirmation->public_token));

        $this->get(route('confirmations.public.signed', $confirmation->public_token))
            ->assertOk()
            ->assertSee('Download PDF')
            ->assertSee('Account aanmaken');

        $this->get(route('confirmations.public.pdf', $confirmation->public_token))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
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

    public function test_contacts_overview_links_to_contact_edit_page(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Voorbeeld B.V.',
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard.contacts'))
            ->assertOk()
            ->assertSee('Bewerken')
            ->assertSee(route('dashboard.contacts.edit', $contact), false);
    }

    public function test_contact_edit_page_prefills_existing_details(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Voorbeeld B.V.',
            'contact_first_name' => 'Piet',
            'contact_email' => 'piet@example.test',
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard.contacts.edit', $contact))
            ->assertOk()
            ->assertSee('Opdrachtgever bewerken')
            ->assertSee('Voorbeeld B.V.')
            ->assertSee('piet@example.test')
            ->assertSee('Wijzigingen opslaan');
    }

    public function test_authenticated_user_can_update_a_contact(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'Oude naam B.V.',
            'contact_email' => 'oud@example.test',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('dashboard.contacts.update', $contact), [
                'company_name' => 'Nieuwe naam B.V.',
                'kvk_number' => '12345678',
                'street_name' => 'Herengracht',
                'house_number' => '10',
                'house_number_addition' => 'B',
                'postal_code' => '1015AB',
                'city' => 'Amsterdam',
                'country' => 'Nederland',
                'contact_first_name' => 'Anne',
                'contact_last_name' => 'Jansen',
                'contact_email' => 'anne@example.test',
                'contact_phone' => '0698765432',
            ]);

        $response->assertRedirect(route('dashboard.contacts'));

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'user_id' => $user->id,
            'company_name' => 'Nieuwe naam B.V.',
            'house_number_addition' => 'B',
            'contact_email' => 'anne@example.test',
            'contact_phone' => '0698765432',
        ]);
    }

    public function test_user_cannot_update_another_users_contact(): void
    {
        $user = User::factory()->create();
        $otherContact = Contact::factory()->create([
            'company_name' => 'Ander bedrijf B.V.',
            'contact_email' => 'ander@example.test',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('dashboard.contacts.update', $otherContact), [
                'company_name' => 'Ongewenste wijziging B.V.',
                'street_name' => 'Herengracht',
                'house_number' => '10',
                'postal_code' => '1015AB',
                'city' => 'Amsterdam',
                'contact_first_name' => 'Anne',
                'contact_last_name' => 'Jansen',
                'contact_email' => 'anne@example.test',
                'contact_phone' => '0698765432',
            ]);

        $response->assertNotFound();

        $this->assertSame('Ander bedrijf B.V.', $otherContact->fresh()->company_name);
        $this->assertSame('ander@example.test', $otherContact->fresh()->contact_email);
    }

    private function signatureDataUri(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';
    }
}
