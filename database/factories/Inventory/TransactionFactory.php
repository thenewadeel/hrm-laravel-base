<?php

namespace Database\Factories\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = \App\Models\Inventory\Transaction::class;

    public function definition()
    {
        return [
            'store_id' => \App\Models\Inventory\Store::factory(),
            'created_by' => User::factory(),
            'type' => $this->faker->randomElement(['incoming', 'outgoing', 'adjustment']),
            'status' => 'draft',
            'reference' => 'TRX' . $this->faker->unique()->numberBetween(10000, 99999),
            'notes' => $this->faker->sentence,
            'transaction_date' => $this->faker->dateTimeThisYear(),
        ];
    }

    public function finalized()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'finalized',
                'finalized_at' => now(),
            ];
        });
    }

    public function incoming()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'incoming',
            ];
        });
    }

    public function outgoing()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'outgoing',
            ];
        });
    }
}
