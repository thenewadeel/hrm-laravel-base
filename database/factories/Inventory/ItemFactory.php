<?php

namespace Database\Factories\Inventory;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = \App\Models\Inventory\Item::class;

    public function definition()
    {
        return [
            // 'organization_id' => Organization::factory(),
            'name' => $this->faker->word,
            'sku' => 'SKU' . $this->faker->unique()->numberBetween(10000, 99999),
            'description' => $this->faker->sentence,
            'category' => $this->faker->randomElement(['electronics', 'office', 'furniture', 'supplies']),
            'unit' => 'pcs',
            'cost_price' => $this->faker->numberBetween(1000, 5000),
            'selling_price' => $this->faker->numberBetween(6000, 10000),
            'reorder_level' => 10,
            'is_active' => true,
            'head_id' => null
        ];
    }
}
