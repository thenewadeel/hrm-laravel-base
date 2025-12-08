<?php

namespace Database\Factories\Membership;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership\MemberCard>
 */
class MemberCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => 1,
            'member_id' => \App\Models\Membership\Member::factory(),
            'card_number' => 'CARD-'.$this->faker->unique()->numerify('##########'),
            'card_type' => $this->faker->randomElement(['standard', 'premium', 'family', 'corporate']),
            'template' => $this->faker->randomElement(['modern', 'classic', 'corporate', 'family', 'minimal']),
            'status' => $this->faker->randomElement(['active', 'expired', 'lost', 'damaged']),
            'issue_date' => $this->faker->dateTimeBetween('-2 years', '-1 month'),
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
            'qr_code_path' => 'qr-codes/'.$this->faker->uuid().'.png',
            'barcode_path' => 'barcodes/'.$this->faker->uuid().'.png',
            'design_settings' => [
                'primary_color' => $this->faker->hexColor(),
                'secondary_color' => $this->faker->hexColor(),
                'font_family' => $this->faker->randomElement(['Arial', 'Helvetica', 'Times New Roman']),
                'layout' => $this->faker->randomElement(['horizontal', 'vertical']),
            ],
            'notes' => $this->faker->optional(0.3)->sentence(),
            'print_count' => $this->faker->numberBetween(0, 5),
            'last_printed_at' => $this->faker->optional(0.7)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'card_type' => 'standard',
            'template' => 'modern',
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'card_type' => 'premium',
            'template' => 'classic',
        ]);
    }

    public function family(): static
    {
        return $this->state(fn (array $attributes) => [
            'card_type' => 'family',
            'template' => 'family',
        ]);
    }

    public function corporate(): static
    {
        return $this->state(fn (array $attributes) => [
            'card_type' => 'corporate',
            'template' => 'corporate',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expiry_date' => now()->addMonths(rand(1, 12)),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expiry_date' => now()->subMonths(rand(1, 6)),
        ]);
    }
}
