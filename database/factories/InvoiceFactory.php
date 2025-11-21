<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'customer_id' => Customer::factory(),
            'vendor_id' => Vendor::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numberBetween(1000, 9999),
            'invoice_date' => fake()->date(),
            'due_date' => fake()->dateTimeBetween('+1 week', '+90 days'),
            'total_amount' => fake()->randomFloat(100, 10000),
            'tax_amount' => fake()->randomFloat(0, 2000),
            'status' => fake()->randomElement(['draft', 'sent', 'paid']),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
