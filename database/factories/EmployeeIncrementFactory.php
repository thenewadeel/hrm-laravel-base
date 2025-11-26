<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeIncrement>
 */
class EmployeeIncrementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'increment_type' => $this->faker->randomElement(['percentage', 'fixed_amount']),
            'increment_value' => $this->faker->numberBetween(5, 20),
            'previous_salary' => $this->faker->numberBetween(3000, 8000),
            'new_salary' => $this->faker->numberBetween(4000, 10000),
            'effective_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'reason' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['pending', 'approved', 'implemented']),
            'approval_notes' => $this->faker->optional()->sentence,
        ];
    }
}
