<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirects_to_dashboard(): void
    {
        $response = $this->post(route('register.store'), [
            'first_name' => 'Wouter',
            'last_name' => 'Meens',
            'kvk_number' => '12345678',
            'company_name' => 'Opdrachtbevestiging',
            'street_name' => 'Dorpsstraat',
            'house_number' => '12',
            'house_number_addition' => 'A',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
            'country' => 'Nederland',
            'email' => 'wouter@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'wouter@example.com',
            'first_name' => 'Wouter',
            'last_name' => 'Meens',
            'company_name' => 'Opdrachtbevestiging',
        ]);
    }

    public function test_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Wouter',
            'last_name' => 'Meens',
            'kvk_number' => '12345678',
            'company_name' => 'Opdrachtbevestiging',
            'street_name' => 'Dorpsstraat',
            'house_number' => '12',
            'house_number_addition' => 'A',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
            'country' => 'Nederland',
            'email' => 'wouter@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_kvk_lookup_returns_company_and_address_data(): void
    {
        config()->set('kvk.api_key', 'test-key');
        config()->set('kvk.base_url', 'https://api.kvk.nl/test/api/v1');

        Http::fake([
            'https://api.kvk.nl/test/api/v1/basisprofielen/12345678' => Http::response([
                'naam' => 'Opdrachtbevestiging B.V.',
                'hoofdvestiging' => [
                    'adressen' => [
                        [
                            'type' => 'bezoekadres',
                            'straatnaam' => 'Dorpsstraat',
                            'huisnummer' => 12,
                            'huisnummerToevoeging' => 'A',
                            'postcode' => '1234AB',
                            'plaats' => 'Amsterdam',
                            'land' => 'Nederland',
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->postJson(route('kvk.lookup'), [
            'kvk_number' => '12345678',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.company_name', 'Opdrachtbevestiging B.V.')
            ->assertJsonPath('data.street_name', 'Dorpsstraat')
            ->assertJsonPath('data.house_number', '12')
            ->assertJsonPath('data.house_number_addition', 'A')
            ->assertJsonPath('data.postal_code', '1234AB')
            ->assertJsonPath('data.city', 'Amsterdam')
            ->assertJsonPath('data.country', 'Nederland');
    }
}
