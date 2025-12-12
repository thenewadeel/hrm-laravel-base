<?php

// database/factories/Accounting/JournalEntryFactory.php

namespace Database\Factories\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference_number' => 'JE-'.$this->faker->unique()->numberBetween(1000, 9999),
            'entry_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['draft', 'posted', 'void']),
            'created_by' => 1, // Use fixed ID for testing
            'approved_by' => null, // Don't create random users
            'posted_at' => $this->faker->optional(0.3)->dateTime(), // 30% chance of having posted_at
            'organization_id' => 1,

        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'posted_at' => null,
        ]);
    }

    public function posted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'posted',
            'posted_at' => now(),
        ]);
    }

    public function void(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'void',
        ]);
    }

    public function approvedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => $user->id,
        ]);
    }
}
