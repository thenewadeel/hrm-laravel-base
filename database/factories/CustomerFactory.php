<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->countryCode(),
            'tax_id' => fake()->numerify('TX-'),
            'customer_type' => fake()->randomElement(['BUSINESS', 'INDIVIDUAL', 'GOVERNMENT']),
            'credit_limit' => fake()->randomFloat(1000, 50000),
            'opening_balance' => fake()->randomFloat(0, 10000),
            'current_balance' => fake()->randomFloat(0, 10000),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}