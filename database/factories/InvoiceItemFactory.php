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
            'invoice_id' => Invoice::factory(),
            'description' => fake()->sentence(),
            'quantity' => fake()->randomFloat(1, 100),
            'unit_price' => fake()->randomFloat(10, 1000),
            'total_amount' => fake()->randomFloat(10, 10000),
        ];
    }
}
}
