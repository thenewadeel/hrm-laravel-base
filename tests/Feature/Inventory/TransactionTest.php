<?php

namespace Tests\Feature\Inventory;

use Tests\Traits\SetupInventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase, SetupInventory;

    /** @test */
    public function it_can_create_a_draft_transaction()
    {
        $setup = $this->createInventorySetup();

        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX001',
            'notes' => 'Initial stock'
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'draft',
                'type' => 'incoming',
                'reference' => 'TRX001'
            ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'status' => 'draft',
            'reference' => 'TRX001',
            'store_id' => $setup['store']->id
        ]);
    }

    /** @test */
    public function it_can_add_items_to_draft_transaction()
    {
        $setup = $this->createDraftTransactionWithItems();

        $newItem = $setup['items']->last();

        $response = $this->actingAs($setup['user'])
            ->postJson("/api/inventory/transactions/{$setup['transaction']->id}/items", [
                'item_id' => $newItem->id,
                'quantity' => 10,
                'unit_price' => 25.50
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('inventory_transaction_items', [
            'transaction_id' => $setup['transaction']->id,
            'item_id' => $newItem->id,
            'quantity' => 10
        ]);
    }

    /** @test */
    public function it_can_finalize_transaction_and_update_store_inventory()
    {
        $setup = $this->createDraftTransactionWithItems();

        $response = $this->actingAs($setup['user'])
            ->postJson("/api/inventory/transactions/{$setup['transaction']->id}/finalize");

        $response->assertStatus(200)
            ->assertJson(['status' => 'finalized']);

        // Check transaction status updated
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $setup['transaction']->id,
            'status' => 'finalized'
        ]);

        // Check store inventory updated
        foreach ($setup['transactionItems'] as $transactionItem) {
            $this->assertDatabaseHas('inventory_store_items', [
                'store_id' => $setup['store']->id,
                'item_id' => $transactionItem->item_id,
                'quantity' => $transactionItem->quantity
            ]);
        }
    }

    /** @test */
    public function it_cannot_modify_finalized_transaction()
    {
        $setup = $this->createFinalizedTransaction();

        $response = $this->actingAs($setup['user'])
            ->postJson("/api/inventory/transactions/{$setup['transaction']->id}/items", [
                'item_id' => $setup['items']->first()->id,
                'quantity' => 10,
                'unit_price' => 15.00
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_calculate_transaction_totals()
    {
        $setup = $this->createDraftTransactionWithItems(null, null, 2);

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/transactions/{$setup['transaction']->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'total_quantity',
                'total_value',
                'items' => [
                    '*' => [
                        'id',
                        'quantity',
                        'unit_price',
                        'total_price'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_list_transactions_by_status()
    {
        $setup = $this->createInventorySetup();

        // Create multiple transactions with different statuses
        $this->createDraftTransactionWithItems($setup['store'], $setup['user']);
        $this->createFinalizedTransaction($setup['store'], $setup['user']);

        $response = $this->actingAs($setup['user'])
            ->getJson('/api/inventory/transactions?status=draft');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'draft');
    }
}
