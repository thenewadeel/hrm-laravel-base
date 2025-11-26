<?php

namespace Database\Factories;

use App\Models\JobPosition;
use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobPositionFactory extends Factory
{
    protected $model = JobPosition::class;

    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'description' => fake()->paragraph(),
            'organization_unit_id' => OrganizationUnit::factory(),
            'min_salary' => fake()->numberBetween(30000, 60000),
            'max_salary' => fake()->numberBetween(60000, 120000),
            'requirements' => fake()->paragraph(),
            'is_active' => fake()->boolean(90),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
