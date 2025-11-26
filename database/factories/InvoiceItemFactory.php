<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = InvoiceItem::class;

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
