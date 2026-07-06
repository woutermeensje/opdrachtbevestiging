<?php

namespace Tests\Feature;

use App\Mail\ConfirmationInvitationMail;
use App\Models\Confirmation;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
        $this->assertSame('<p>Ontwikkeling van een <strong>marketingwebsite</strong>.</p>', $confirmation->description);

        Mail::assertSent(ConfirmationInvitationMail::class, function (ConfirmationInvitationMail $mail) use ($confirmation): bool {
            $mail->assertSeeInHtml('<strong>marketingwebsite</strong>', false);

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
            ->assertSee('name="title"', false)
            ->assertSee('data-rich-editor', false)
            ->assertSee('name="contact_id"', false)
            ->assertSee('Verzenden')
            ->assertDontSee('name="total_value"', false)
            ->assertDontSee('name="status"', false);
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
        $this->assertNull($confirmation->signhost_transaction_id);

        Mail::assertSent(ConfirmationInvitationMail::class, function (ConfirmationInvitationMail $mail) use ($confirmation): bool {
            $mail->assertHasSubject('Opdrachtbevestiging OB-SENDTEST: Nieuwe website opdracht');
            $mail->assertSeeInHtml('Deze e-mail bevat de volledige opdrachtbevestiging');
            $mail->assertSeeInHtml('Ontwikkeling van een marketingwebsite.');
            $mail->assertSeeInText('Ontwikkeling van een marketingwebsite.');

            $this->assertSame([], $mail->attachments());

            return $mail->hasTo($confirmation->client_email);
        });

        Http::assertNothingSent();
    }

    public function test_public_recipient_can_accept_confirmation(): void
    {
        $confirmation = Confirmation::factory()->create([
            'status' => 'verzonden',
            'signed_at' => null,
        ]);

        $response = $this->post(route('confirmations.public.accept', $confirmation->public_token), [
            'signer_name' => 'Jan de Vries',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect(route('confirmations.public.show', $confirmation->public_token));

        $confirmation->refresh();

        $this->assertSame('getekend', $confirmation->status);
        $this->assertSame('Jan de Vries', $confirmation->signer_name);
        $this->assertNotNull($confirmation->signed_at);
    }

    public function test_authenticated_user_can_store_a_contact(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.contacts.store'), [
                'company_name' => 'Voorbeeld B.V.',
                'kvk_number' => '12345678',
                'street_name' => 'Keizersgracht',
                'house_number' => '1',
                'house_number_addition' => 'A',
                'postal_code' => '1015CJ',
                'city' => 'Amsterdam',
                'country' => 'Nederland',
                'contact_first_name' => 'Piet',
                'contact_last_name' => 'de Boer',
                'contact_email' => 'piet@example.test',
            ]);

        $response->assertRedirect(route('dashboard.contacts'));

        $this->assertDatabaseHas('contacts', [
            'user_id' => $user->id,
            'company_name' => 'Voorbeeld B.V.',
            'contact_email' => 'piet@example.test',
        ]);
    }
}
