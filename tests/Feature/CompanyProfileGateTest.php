<?php

namespace Tests\Feature;

use App\Models\Confirmation;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyProfileGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_menu_lists_profile_subcategories(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('dashboard.profile'))
            ->assertOk()
            ->assertSee('Mijn profiel')
            ->assertSee('Mijn account')
            ->assertSee('Bedrijfsgegevens')
            ->assertSee('Vaste documentgegevens')
            ->assertSee(route('dashboard.profile.company'), false)
            ->assertSee(route('dashboard.profile.documents'), false);
    }

    public function test_profile_subpages_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard.profile'))->assertOk()->assertSee('Mijn account');
        $this->actingAs($user)->get(route('dashboard.profile.company'))->assertOk()->assertSee('KVK-gegevens');
        $this->actingAs($user)->get(route('dashboard.profile.documents'))->assertOk()->assertSee('Basis afspraken');
    }

    public function test_user_can_update_account_details(): void
    {
        $user = User::factory()->create([
            'email' => 'oude@example.test',
        ]);

        $response = $this
            ->actingAs($user)
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('dashboard.profile.account.update'), [
                'name' => 'Nieuwe gebruiker',
                'first_name' => 'Nieuwe',
                'last_name' => 'Gebruiker',
                'email' => 'nieuwe@example.test',
                'phone_number' => '0612345678',
            ]);

        $response->assertRedirect(route('dashboard.profile'));

        $user->refresh();
        $this->assertSame('Nieuwe gebruiker', $user->name);
        $this->assertSame('Nieuwe', $user->first_name);
        $this->assertSame('Gebruiker', $user->last_name);
        $this->assertSame('nieuwe@example.test', $user->email);
        $this->assertSame('0612345678', $user->phone_number);
    }

    public function test_user_can_update_account_password(): void
    {
        $user = User::factory()->create([
            'password' => 'oudpassword123',
        ]);

        $response = $this
            ->actingAs($user)
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('dashboard.profile.password.update'), [
                'current_password' => 'oudpassword123',
                'password' => 'nieuwpassword123',
                'password_confirmation' => 'nieuwpassword123',
            ]);

        $response->assertRedirect(route('dashboard.profile'));
        $this->assertTrue(password_verify('nieuwpassword123', $user->fresh()->password));
    }

    public function test_user_without_company_profile_is_redirected_away_from_create(): void
    {
        $user = User::factory()->create([
            'company_name' => null,
            'kvk_number' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.create'));

        $response->assertRedirect(route('dashboard.profile.company'));
        $response->assertSessionHas('status');
    }

    public function test_user_without_company_profile_cannot_store_a_confirmation(): void
    {
        $user = User::factory()->create([
            'company_name' => null,
            'kvk_number' => null,
        ]);
        $contact = Contact::factory()->create(['user_id' => $user->id]);

        $response = $this
            ->actingAs($user)
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('dashboard.create.store'), [
                'title' => 'Test opdracht',
                'contact_id' => $contact->id,
                'description' => '<p>Omschrijving</p>',
            ]);

        $response->assertRedirect(route('dashboard.profile.company'));
        $this->assertSame(0, Confirmation::query()->count());
    }

    public function test_user_without_company_profile_cannot_send_a_confirmation(): void
    {
        $user = User::factory()->create([
            'company_name' => null,
            'kvk_number' => null,
        ]);
        $confirmation = Confirmation::factory()->create([
            'user_id' => $user->id,
            'status' => 'concept',
        ]);

        $response = $this
            ->actingAs($user)
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('dashboard.confirmations.send', $confirmation));

        $response->assertRedirect(route('dashboard.profile.company'));
        $this->assertSame('concept', $confirmation->fresh()->status);
    }

    public function test_user_can_complete_company_profile(): void
    {
        $user = User::factory()->create([
            'company_name' => null,
            'kvk_number' => null,
        ]);

        $this->assertFalse($user->hasCompletedCompanyProfile());

        $response = $this
            ->actingAs($user)
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('dashboard.profile.company.update'), [
                'company_name' => 'Acme B.V.',
                'kvk_number' => '12345678',
                'street_name' => 'Keizersgracht',
                'house_number' => '1',
                'postal_code' => '1015CJ',
                'city' => 'Amsterdam',
                'country' => 'Nederland',
            ]);

        $response->assertRedirect(route('dashboard.profile.company'));

        $user->refresh();
        $this->assertTrue($user->hasCompletedCompanyProfile());
        $this->assertSame('Acme B.V.', $user->company_name);
        $this->assertSame('12345678', $user->kvk_number);

        $this->actingAs($user)->get(route('dashboard.create'))->assertOk();
    }

    public function test_user_can_store_document_defaults_on_profile(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->post(route('dashboard.profile.documents.update'), [
                'company_logo' => UploadedFile::fake()->create('logo.jpg', 12, 'image/jpeg'),
                'terms' => UploadedFile::fake()->create('algemene-voorwaarden.pdf', 32, 'application/pdf'),
                'default_agreements' => '<p>Betaling binnen <strong>14 dagen</strong>.</p><script>alert("x")</script>',
            ]);

        $response->assertRedirect(route('dashboard.profile.documents'));

        $user->refresh();
        $this->assertSame('logo.jpg', $user->company_logo_original_name);
        $this->assertSame('algemene-voorwaarden.pdf', $user->terms_original_name);
        $this->assertSame('<p>Betaling binnen <strong>14 dagen</strong>.</p>', $user->default_agreements);

        Storage::disk('local')->assertExists($user->company_logo_path);
        Storage::disk('local')->assertExists($user->terms_path);
    }
}
