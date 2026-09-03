<?php

namespace Database\Factories;

use App\Models\Confirmation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Confirmation>
 */
class ConfirmationFactory extends Factory
{
    protected $model = Confirmation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'contact_id' => null,
            'reference' => 'OB-'.fake()->unique()->numerify('######'),
            'title' => fake()->sentence(3),
            'client_name' => fake()->company(),
            'client_contact_name' => fake()->name(),
            'client_email' => fake()->companyEmail(),
            'client_kvk_number' => fake()->numerify('########'),
            'description' => fake()->paragraph(),
            'footer_note' => null,
            'public_token' => fake()->sha1(),
            'total_value' => fake()->randomFloat(2, 250, 7500),
            'status' => fake()->randomElement(['concept', 'verzonden', 'getekend']),
            'sender_name' => fake()->name(),
            'sender_email' => fake()->safeEmail(),
            'attachment_path' => null,
            'attachment_original_name' => null,
            'attachment_mime_type' => null,
            'quote_path' => null,
            'quote_original_name' => null,
            'quote_mime_type' => null,
            'agreement_date' => now()->toDateString(),
            'sent_at' => now(),
            'viewed_at' => null,
            'signed_at' => null,
            'signer_name' => null,
            'signer_ip' => null,
            'signer_user_agent' => null,
            'signer_signature_path' => null,
            'signer_signature_mime_type' => null,
            'expires_at' => now()->addDays(14)->toDateString(),
        ];
    }
}
