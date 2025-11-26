<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(6, 20);
        $endHour = ($startHour + fake()->numberBetween(6, 10)) % 24;

        return [
            'organization_id' => \App\Models\Organization::factory(),
            'name' => fake()->randomElement(['Morning', 'Afternoon', 'Night', 'Weekend']).' Shift',
            'code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:00', $endHour),
            'days_of_week' => fake()->randomElements(
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                fake()->numberBetween(1, 7)
            ),
            'working_hours' => fake()->randomFloat(1, 4, 12),
            'is_active' => fake()->boolean(95),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
