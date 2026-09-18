<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'store_id' => Store::factory(),
            'item_id' => Item::factory(),
            'created_by' => User::factory(),
            'type' => $this->faker->randomElement(['incoming', 'outgoing', 'adjustment']),
            'status' => 'draft',
            'reference' => 'TRX'.$this->faker->unique()->numberBetween(10000, 99999),
            'notes' => $this->faker->sentence,
            'transaction_date' => $this->faker->dateTimeThisYear(),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Transaction $transaction) {
            if (! empty($transaction->organization_id) || empty($transaction->store_id)) {
                return;
            }

            $store = Store::query()->withoutGlobalScopes()->find($transaction->store_id);

            if ($store && $store->organization_unit) {
                $transaction->organization_id = $store->organization_unit->organization_id;
            }
        });
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
