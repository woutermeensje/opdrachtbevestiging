<?php

namespace Tests\Feature;

use App\Models\Confirmation;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyProfileGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_company_profile_is_redirected_away_from_create(): void
    {
        $user = User::factory()->create([
            'company_name' => null,
            'kvk_number' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard.create'));

        $response->assertRedirect(route('dashboard.profile'));
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

        $response->assertRedirect(route('dashboard.profile'));
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

        $response->assertRedirect(route('dashboard.profile'));
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
            ->post(route('dashboard.profile.update'), [
                'company_name' => 'Acme B.V.',
                'kvk_number' => '12345678',
                'street_name' => 'Keizersgracht',
                'house_number' => '1',
                'postal_code' => '1015CJ',
                'city' => 'Amsterdam',
                'country' => 'Nederland',
            ]);

        $response->assertRedirect(route('dashboard.profile'));

        $user->refresh();
        $this->assertTrue($user->hasCompletedCompanyProfile());
        $this->assertSame('Acme B.V.', $user->company_name);
        $this->assertSame('12345678', $user->kvk_number);

        $this->actingAs($user)->get(route('dashboard.create'))->assertOk();
    }
}
