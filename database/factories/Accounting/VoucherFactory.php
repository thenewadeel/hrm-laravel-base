<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\Voucher;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounting\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * The name of factory's corresponding model.
     */
    protected $model = Voucher::class;

    /**
     * Define model's default state.
     */
    public function definition(): array
    {
        $types = ['sales', 'sales_return', 'purchase', 'purchase_return', 'salary', 'expense', 'fixed_asset', 'depreciation'];

        return [
            'organization_id' => Organization::factory(),
            'type' => fake()->randomElement($types),
            'number' => 'SALES-2025-'.str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'description' => fake()->sentence(),
            'notes' => fake()->optional()->sentence(),
            'status' => 'draft',
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    /**
     * Indicate that voucher is posted.
     */
    public function posted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'posted',
        ]);
    }

    /**
     * Create a sales voucher.
     */
    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sales',
        ]);
    }

    /**
     * Create a purchase voucher.
     */
    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'purchase',
        ]);
    }

    /**
     * Create an expense voucher.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
        ]);
    }

    /**
     * Create a salary voucher.
     */
    public function salary(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'salary',
        ]);
    }
}
