<?php

namespace Tests\Feature\Inventory;

use Tests\TestCase;
use App\Models\Inventory\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;
use Tests\Traits\SetupOrganization;

class InventoryTransactionControllerTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
    }

    #[Test]
    public function it_displays_transactions_index()
    {
        $setup = $this->createDraftTransactionWithItems();

        $response = $this->get(route('inventory.transactions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.transactions.index');
        $response->assertViewHas('transactions');
        $response->assertViewHas('stores');

        $transactions = $response->viewData('transactions');
        $this->assertCount(1, $transactions);
    }

    #[Test]
    public function it_shows_transaction_creation_form()
    {
        $response = $this->get(route('inventory.transactions.create'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.transactions.create');
        $response->assertViewHas('stores');
        $response->assertViewHas('items');
    }

    #[Test]
    public function it_stores_new_transaction_with_items()
    {
        $store = $this->store;
        $items = $this->createInventorySetup()['items']->take(2);

        $transactionData = [
            'store_id' => $store->id,
            'type' => 'in',
            'reference' => 'TEST-001',
            'transaction_date' => now()->format('Y-m-d'),
            'notes' => 'Test transaction',
            'items' => [
                [
                    'item_id' => $items[0]->id,
                    'quantity' => 10,
                    'unit_price' => 1000,
                    'notes' => 'First item'
                ],
                [
                    'item_id' => $items[1]->id,
                    'quantity' => 5,
                    'unit_price' => 2000,
                    'notes' => 'Second item'
                ]
            ]
        ];

        $response = $this->post(route('inventory.transactions.store'), $transactionData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_transactions', [
            'reference' => 'TEST-001',
            'type' => 'in'
        ]);
    }
}
