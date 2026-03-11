<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company(),
            'kvk_number' => fake()->numerify('########'),
            'street_name' => fake()->streetName(),
            'house_number' => fake()->buildingNumber(),
            'house_number_addition' => null,
            'postal_code' => strtoupper(fake()->bothify('####??')),
            'city' => fake()->city(),
            'country' => 'Nederland',
            'contact_first_name' => fake()->firstName(),
            'contact_last_name' => fake()->lastName(),
            'contact_email' => fake()->safeEmail(),
        ];
    }
}
