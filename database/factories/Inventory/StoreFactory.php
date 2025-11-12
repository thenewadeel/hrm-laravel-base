<?php

namespace Database\Factories\Inventory;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = \App\Models\Inventory\Store::class;

    public function definition()
    {
        return [
            'organization_unit_id' => OrganizationUnit::factory(),
            'name' => $this->faker->word . ' Store',
            'code' => 'ST' . $this->faker->unique()->numberBetween(1000, 9999),
            'location' => $this->faker->streetAddress,
            'description' => $this->faker->sentence,
            'is_active' => true,
        ];
    }
}
