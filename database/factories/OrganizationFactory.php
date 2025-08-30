<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'is_active' => $this->faker->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }



    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
