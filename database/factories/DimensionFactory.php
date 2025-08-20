<?php
// database/factories/DimensionFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DimensionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => $this->faker->unique()->bothify('DIM-#####'),
            'type' => $this->faker->randomElement(['cost_center', 'project', 'branch', 'department', 'team']),
            'description' => $this->faker->sentence(),
        ];
    }
}
