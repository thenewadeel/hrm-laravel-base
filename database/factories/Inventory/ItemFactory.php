<?php

namespace Database\Factories\Inventory;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = \App\Models\Inventory\Item::class;
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->words(3, true),
            'sku' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(['Electronics', 'Office Supplies', 'Furniture', 'Tools']),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'box', 'meter']),
            'cost_price' => $this->faker->numberBetween(100, 5000),
            'selling_price' => $this->faker->numberBetween(1000, 10000),
            'reorder_level' => 10,
            'is_active' => true,
            'head_id' => null
        ];
    }
}
