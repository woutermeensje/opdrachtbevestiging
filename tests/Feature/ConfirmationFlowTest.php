<?php

namespace Tests\Feature;

use App\Mail\ConfirmationInvitationMail;
use App\Models\Confirmation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConfirmationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_confirmation(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.create.store'), [
                'title' => 'Nieuwe website opdracht',
                'client_name' => 'Acme B.V.',
                'client_email' => 'info@acme.test',
                'description' => 'Ontwikkeling van een marketingwebsite.',
                'total_value' => '2499.95',
                'status' => 'verzonden',
                'agreement_date' => '2026-03-08',
                'sent_at' => '2026-03-09',
                'signed_at' => null,
                'expires_at' => '2026-03-23',
            ]);

        $confirmation = Confirmation::query()->first();

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));
        $this->assertNotNull($confirmation);
        $this->assertSame($user->id, $confirmation->user_id);
        $this->assertSame('Acme B.V.', $confirmation->client_name);
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

        $user = User::factory()->create();
        $confirmation = Confirmation::factory()->create([
            'user_id' => $user->id,
            'status' => 'concept',
            'sent_at' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('dashboard.confirmations.send', $confirmation));

        $response->assertRedirect(route('dashboard.confirmations.show', $confirmation));

        Mail::assertSent(ConfirmationInvitationMail::class, function (ConfirmationInvitationMail $mail) use ($confirmation) {
            return $mail->confirmation->is($confirmation);
        });

        $confirmation->refresh();

        $this->assertSame('verzonden', $confirmation->status);
        $this->assertNotNull($confirmation->sent_at);
    }

    public function test_public_recipient_can_sign_confirmation(): void
    {
        $confirmation = Confirmation::factory()->create([
            'status' => 'verzonden',
            'signed_at' => null,
        ]);

        $response = $this->post(route('confirmations.public.sign', $confirmation->public_token), [
            'signer_name' => 'Jan de Vries',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect(route('confirmations.public.show', $confirmation->public_token));

        $confirmation->refresh();

        $this->assertSame('getekend', $confirmation->status);
        $this->assertSame('Jan de Vries', $confirmation->signer_name);
        $this->assertNotNull($confirmation->signed_at);
    }
}
