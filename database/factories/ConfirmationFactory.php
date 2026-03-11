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
            'public_token' => fake()->sha1(),
            'total_value' => fake()->randomFloat(2, 250, 7500),
            'status' => fake()->randomElement(['concept', 'verzonden', 'getekend']),
            'sender_name' => fake()->name(),
            'sender_email' => fake()->safeEmail(),
            'agreement_date' => now()->toDateString(),
            'sent_at' => now(),
            'viewed_at' => null,
            'signed_at' => null,
            'signer_name' => null,
            'signer_ip' => null,
            'signer_user_agent' => null,
            'signhost_transaction_id' => null,
            'signhost_status' => 'draft',
            'signhost_file_id' => null,
            'signhost_receipt_path' => null,
            'signhost_signed_document_path' => null,
            'signhost_completed_at' => null,
            'expires_at' => now()->addDays(14)->toDateString(),
        ];
    }
}
