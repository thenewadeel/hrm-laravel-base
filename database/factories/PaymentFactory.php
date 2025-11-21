<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'customer_id' => Customer::factory(),
            'invoice_id' => Invoice::factory(),
            'payment_date' => fake()->date(),
            'amount' => fake()->randomFloat(10, 10000),
            'payment_method' => fake()->randomElement(['cash', 'check', 'bank_transfer', 'credit_card']),
            'reference_number' => 'PAY-' . fake()->unique()->numberBetween(1000, 9999),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['pending', 'received', 'processed']),
            'created_by' => User::factory(),
        ];
    }
}
}
