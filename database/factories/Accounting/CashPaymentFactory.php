<?php

namespace Database\Factories\Accounting;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\CashPayment>
 */
class CashPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'voucher_number' => 'VCH-'.str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'date' => $this->faker->date(),
            'paid_to' => $this->faker->name(),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'purpose' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
