<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory\TransactionItem;
use Tests\Traits\SetupInventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupOrganization;

class TransactionTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
    }

    #[Test]
    public function it_can_create_draft_transaction_without_items()
    {
        $setup = $this->createInventorySetup();

        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX001',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Empty draft transaction'
            // ✅ No items field - testing without items
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'status' => 'draft',
                    'type' => 'incoming',
                    'reference' => 'TRX001',
                    'notes' => 'Empty draft transaction',
                    'is_draft' => true,
                    'is_incoming' => true,
                ]
            ]);

        $transaction = $response->json('data');

        // Verify transaction was created
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $transaction['id'],
            'reference' => 'TRX001',
            'status' => 'draft',
            'notes' => 'Empty draft transaction'
        ]);

        // Verify no items were created
        $this->assertEquals(0, \App\Models\Inventory\TransactionItem::where('transaction_id', $transaction['id'])->count());

        // Verify items array exists but is empty in response
        $this->assertArrayHasKey('items', $transaction);
        $this->assertIsArray($transaction['items']);
        $this->assertEmpty($transaction['items']);
    }

    #[Test]
    public function it_can_create_draft_transaction_with_items()
    {
        $setup = $this->createInventorySetup();

        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX002',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Transaction with initial items',
            'items' => [
                [
                    'item_id' => $setup['items']->first()->id,
                    'quantity' => 5,
                    'unit_price' => 15.00, // Dollars
                    'notes' => 'First item'
                ],
                [
                    'item_id' => $setup['items']->last()->id,
                    'quantity' => 3,
                    'unit_price' => 20.00, // Dollars
                    'notes' => 'Second item'
                ]
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'status' => 'draft',
                    'type' => 'incoming',
                    'reference' => 'TRX002',
                    'notes' => 'Transaction with initial items',
                    'is_draft' => true,
                    'is_incoming' => true,
                ]
            ]);

        $transaction = $response->json('data');

        // Verify transaction was created
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $transaction['id'],
            'reference' => 'TRX002',
            'status' => 'draft'
        ]);

        // Verify items were created
        $this->assertEquals(2, TransactionItem::where('transaction_id', $transaction['id'])->count());

        $this->assertDatabaseHas('inventory_transaction_items', [
            'transaction_id' => $transaction['id'],
            'item_id' => $setup['items']->first()->id,
            'quantity' => 5,
            'unit_price' => 15, //00, // Stored as cents in database
            'notes' => 'First item'
        ]);

        $this->assertDatabaseHas('inventory_transaction_items', [
            'transaction_id' => $transaction['id'],
            'item_id' => $setup['items']->last()->id,
            'quantity' => 3,
            'unit_price' => 20, //00, // Stored as cents in database
            'notes' => 'Second item'
        ]);

        // Verify items are in response (converted back to dollars)
        $this->assertArrayHasKey('items', $transaction);
        $this->assertCount(2, $transaction['items']);

        // Verify item details in response (should be in dollars)
        $this->assertEquals($setup['items']->first()->id, $transaction['items'][0]['item_id']);
        $this->assertEquals(5, $transaction['items'][0]['quantity']);
        $this->assertEquals(15.00, $transaction['items'][0]['unit_price']); // Now in dollars

        // Verify total price calculations
        $this->assertEquals(75.00, $transaction['items'][0]['total_price']); // 5 * 15.00
        $this->assertEquals(60.00, $transaction['items'][1]['total_price']); // 3 * 20.00

        // Verify overall transaction totals
        $this->assertEquals(8, $transaction['total_quantity']); // 5 + 3
        $this->assertEquals(135.00, $transaction['total_value']); // 75.00 + 60.00
    }

    #[Test]
    public function it_can_add_items_to_empty_draft_transaction()
    {
        $setup = $this->createInventorySetup();

        // Create transaction WITHOUT items
        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX003',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            // No items
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $response->assertStatus(201);
        $transaction = $response->json('data');

        // Verify transaction is empty initially
        $this->assertEquals(0, \App\Models\Inventory\TransactionItem::where('transaction_id', $transaction['id'])->count());

        // Add items to the empty transaction
        $items = [
            [
                'item_id' => $setup['items']->first()->id,
                'quantity' => 10,
                'unit_price' => 25.50,
                'notes' => 'Added item'
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson("/api/inventory/transactions/{$transaction['id']}/items", [
                'items' => $items
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Items added to transaction successfully'
            ]);

        // Verify item was added
        $this->assertDatabaseHas('inventory_transaction_items', [
            'transaction_id' => $transaction['id'],
            'item_id' => $setup['items']->first()->id,
            'quantity' => 10,
            'notes' => 'Added item'
        ]);

        // Verify total count
        $this->assertEquals(1, \App\Models\Inventory\TransactionItem::where('transaction_id', $transaction['id'])->count());
    }

    #[Test]
    public function it_can_add_items_to_transaction_with_existing_items()
    {
        $setup = $this->createInventorySetup();

        // Create transaction WITH some initial items
        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX004',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'item_id' => $setup['items'][0]->id,
                    'quantity' => 5,
                    'unit_price' => 10.00
                ]
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $transaction = $response->json('data');

        // Verify initial item exists
        $this->assertEquals(1, \App\Models\Inventory\TransactionItem::where('transaction_id', $transaction['id'])->count());

        // Add more items
        $additionalItems = [
            [
                'item_id' => $setup['items'][1]->id,
                'quantity' => 8,
                'unit_price' => 15.50,
                'notes' => 'Additional item 1'
            ],
            [
                'item_id' => $setup['items'][2]->id,
                'quantity' => 3,
                'unit_price' => 20.00,
                'notes' => 'Additional item 2'
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson("/api/inventory/transactions/{$transaction['id']}/items", [
                'items' => $additionalItems
            ]);

        $response->assertStatus(200);

        // Verify all items exist (1 initial + 2 additional)
        $this->assertEquals(3, \App\Models\Inventory\TransactionItem::where('transaction_id', $transaction['id'])->count());

        $this->assertDatabaseHas('inventory_transaction_items', [
            'transaction_id' => $transaction['id'],
            'item_id' => $setup['items'][0]->id,
            'quantity' => 5
        ]);

        $this->assertDatabaseHas('inventory_transaction_items', [
            'transaction_id' => $transaction['id'],
            'item_id' => $setup['items'][1]->id,
            'quantity' => 8,
            'notes' => 'Additional item 1'
        ]);

        $this->assertDatabaseHas('inventory_transaction_items', [
            'transaction_id' => $transaction['id'],
            'item_id' => $setup['items'][2]->id,
            'quantity' => 3,
            'notes' => 'Additional item 2'
        ]);
    }

    #[Test]
    public function it_can_finalize_transaction_created_without_items_after_adding_items()
    {
        $setup = $this->createInventorySetup();

        // Create empty transaction
        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX005',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            // No items initially
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $transaction = $response->json('data');

        // Add items
        $items = [
            [
                'item_id' => $setup['items']->first()->id,
                'quantity' => 25,
                'unit_price' => 15.50
            ]
        ];

        $this->actingAs($setup['user'])
            ->postJson("/api/inventory/transactions/{$transaction['id']}/items", [
                'items' => $items
            ]);

        // Now finalize the transaction
        $response = $this->actingAs($setup['user'])
            ->putJson("/api/inventory/transactions/{$transaction['id']}/finalize");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Transaction finalized successfully']);

        // Check transaction status updated
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $transaction['id'],
            'status' => 'finalized'
        ]);

        // Check store inventory updated
        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $setup['store']->id,
            'item_id' => $setup['items']->first()->id,
            'quantity' => 25
        ]);
    }

    #[Test]
    public function it_can_finalize_transaction_created_with_items()
    {
        $setup = $this->createInventorySetup();

        // Create transaction WITH items
        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX006',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'item_id' => $setup['items']->first()->id,
                    'quantity' => 30,
                    'unit_price' => 12.50
                ]
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $transaction = $response->json('data');

        // Finalize directly (no need to add items separately)
        $response = $this->actingAs($setup['user'])
            ->putJson("/api/inventory/transactions/{$transaction['id']}/finalize");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Transaction finalized successfully']);

        // Check transaction status
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $transaction['id'],
            'status' => 'finalized'
        ]);

        // Check store inventory
        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $setup['store']->id,
            'item_id' => $setup['items']->first()->id,
            'quantity' => 30
        ]);
    }

    #[Test]
    public function it_cannot_finalize_empty_transaction()
    {
        $setup = $this->createInventorySetup();

        // Create empty transaction
        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX007',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            // No items
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $transaction = $response->json('data');

        // Try to finalize empty transaction
        $response = $this->actingAs($setup['user'])
            ->putJson("/api/inventory/transactions/{$transaction['id']}/finalize");

        // Should fail because transaction has no items
        $response->assertStatus(422); // Or whatever status code your service throws
    }
    #[Test]
    public function it_cannot_add_items_to_finalized_transaction()
    {
        $setup = $this->createInventorySetup();

        // Create and finalize a transaction
        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX008',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'item_id' => $setup['items']->first()->id,
                    'quantity' => 5,
                    'unit_price' => 10.00
                ]
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $transaction = $response->json('data');

        // Finalize the transaction
        $this->actingAs($setup['user'])
            ->putJson("/api/inventory/transactions/{$transaction['id']}/finalize")
            ->assertStatus(200);

        // Try to add items to finalized transaction
        $items = [
            [
                'item_id' => $setup['items']->last()->id,
                'quantity' => 10,
                'unit_price' => 15.00,
                'notes' => 'Should not be added'
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson("/api/inventory/transactions/{$transaction['id']}/items", [
                'items' => $items
            ]);

        // Should be forbidden or return error
        $response->assertStatus(403); // Or 422 depending on your implementation

        // Verify no new items were added
        $this->assertEquals(1, \App\Models\Inventory\TransactionItem::where('transaction_id', $transaction['id'])->count());

        // Verify the attempted item doesn't exist
        $this->assertDatabaseMissing('inventory_transaction_items', [
            'transaction_id' => $transaction['id'],
            'item_id' => $setup['items']->last()->id,
            'notes' => 'Should not be added'
        ]);
    }

    #[Test]
    public function it_can_list_transactions_by_status()
    {
        $setup = $this->createInventorySetup();

        // Create transactions with different statuses
        $draftTransaction = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX-DRAFT-001',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'items' => [[
                'item_id' => $setup['items']->first()->id,
                'quantity' => 10,
                'unit_price' => 15.00
            ]]
        ];

        $finalizedTransaction = [
            'store_id' => $setup['store']->id,
            'type' => 'outgoing',
            'reference' => 'TRX-FINAL-001',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'items' => [[
                'item_id' => $setup['items']->last()->id,
                'quantity' => 5,
                'unit_price' => 20.00
            ]]
        ];

        $anotherDraftTransaction = [
            'store_id' => $setup['store']->id,
            'type' => 'adjustment',
            'reference' => 'TRX-DRAFT-002',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'items' => [[
                'item_id' => $setup['items']->first()->id,
                'quantity' => 3,
                'unit_price' => 8.50
            ]]
        ];

        // Create draft transactions
        $response1 = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $draftTransaction);
        $draftTx1 = $response1->json('data');

        $response2 = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $anotherDraftTransaction);
        $draftTx2 = $response2->json('data');

        // Create and finalize a transaction
        $response3 = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $finalizedTransaction);
        $finalizedTx = $response3->json('data');

        $this->actingAs($setup['user'])
            ->putJson("/api/inventory/transactions/{$finalizedTx['id']}/finalize");

        // Filter by draft status
        $response = $this->actingAs($setup['user'])
            ->getJson('/api/inventory/transactions?status=draft');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertIsArray($data);

        // Should find 2 draft transactions
        $draftTransactions = array_filter($data, fn($tx) => $tx['status'] === 'draft');
        $this->assertCount(2, $draftTransactions);

        // Verify the references of draft transactions
        $draftReferences = array_column($draftTransactions, 'reference');
        $this->assertContains('TRX-DRAFT-001', $draftReferences);
        $this->assertContains('TRX-DRAFT-002', $draftReferences);
        $this->assertNotContains('TRX-FINAL-001', $draftReferences);

        // Filter by finalized status
        $response = $this->actingAs($setup['user'])
            ->getJson('/api/inventory/transactions?status=finalized');

        $response->assertStatus(200);

        $data = $response->json('data');
        $finalizedTransactions = array_filter($data, fn($tx) => $tx['status'] === 'finalized');
        $this->assertCount(1, $finalizedTransactions);
        $this->assertEquals('TRX-FINAL-001', $finalizedTransactions[0]['reference']);
    }

    #[Test]
    public function it_can_calculate_transaction_totals()
    {
        $setup = $this->createInventorySetup();

        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX009',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'item_id' => $setup['items']->first()->id,
                    'quantity' => 10,
                    'unit_price' => 25.50 // 255.00 total
                ],
                [
                    'item_id' => $setup['items']->last()->id,
                    'quantity' => 5,
                    'unit_price' => 15.00 // 75.00 total
                ],
                [
                    'item_id' => $setup['items'][1]->id, // Middle item
                    'quantity' => 3,
                    'unit_price' => 8.75 // 26.25 total
                ]
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $transaction = $response->json('data');

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/transactions/{$transaction['id']}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
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
                ]
            ]);

        // Verify calculations
        $data = $response->json('data');

        // Total quantity: 10 + 5 + 3 = 18
        $this->assertEquals(18, $data['total_quantity']);

        // Total value: (10 * 25.50) + (5 * 15.00) + (3 * 8.75) = 255 + 75 + 26.25 = 356.25
        $this->assertEquals(356.25, $data['total_value']);

        // Verify individual item calculations - find items by their quantities/values
        $items = $data['items'];
        $this->assertCount(3, $items);

        // Find items by their expected values instead of assuming order
        $itemWithQuantity10 = collect($items)->firstWhere('quantity', 10);
        $itemWithQuantity5 = collect($items)->firstWhere('quantity', 5);
        $itemWithQuantity3 = collect($items)->firstWhere('quantity', 3);

        // Check first item (quantity 10)
        $this->assertNotNull($itemWithQuantity10, 'Item with quantity 10 not found');
        $this->assertEquals(25.50, $itemWithQuantity10['unit_price']);
        $this->assertEquals(255.00, $itemWithQuantity10['total_price']); // 10 * 25.50

        // Check second item (quantity 5)
        $this->assertNotNull($itemWithQuantity5, 'Item with quantity 5 not found');
        $this->assertEquals(15.00, $itemWithQuantity5['unit_price']);
        $this->assertEquals(75.00, $itemWithQuantity5['total_price']); // 5 * 15.00

        // Check third item (quantity 3)
        $this->assertNotNull($itemWithQuantity3, 'Item with quantity 3 not found');
        $this->assertEquals(8.75, $itemWithQuantity3['unit_price']);
        $this->assertEquals(26.25, $itemWithQuantity3['total_price']); // 3 * 8.75
    }

    #[Test]
    public function it_cannot_modify_finalized_transaction()
    {
        $setup = $this->createInventorySetup();

        // Create and finalize a transaction
        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX010',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'item_id' => $setup['items']->first()->id,
                    'quantity' => 8,
                    'unit_price' => 12.50
                ]
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $transaction = $response->json('data');

        // Finalize the transaction
        $this->actingAs($setup['user'])
            ->putJson("/api/inventory/transactions/{$transaction['id']}/finalize")
            ->assertStatus(200);

        // Attempt 1: Try to update transaction notes
        $response = $this->actingAs($setup['user'])
            ->putJson("/api/inventory/transactions/{$transaction['id']}", [
                'notes' => 'This should not be allowed'
            ]);

        $response->assertStatus(403); // Should be forbidden

        // Verify notes were not updated
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $transaction['id'],
            'notes' => null // Original notes were null
        ]);

        // Attempt 2: Try to add items (already tested in separate test, but good to have here too)
        $items = [
            [
                'item_id' => $setup['items']->last()->id,
                'quantity' => 5,
                'unit_price' => 10.00
            ]
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson("/api/inventory/transactions/{$transaction['id']}/items", [
                'items' => $items
            ]);

        $response->assertStatus(403); // Should be forbidden

        // Attempt 3: Try to delete the transaction
        $response = $this->actingAs($setup['user'])
            ->deleteJson("/api/inventory/transactions/{$transaction['id']}");

        $response->assertStatus(403); // Should be forbidden

        // Verify transaction still exists and is finalized
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $transaction['id'],
            'status' => 'finalized',
            'deleted_at' => null
        ]);
    }

    #[Test]
    public function it_can_calculate_totals_for_empty_transaction()
    {
        $setup = $this->createInventorySetup();

        // Create transaction without items
        $transactionData = [
            'store_id' => $setup['store']->id,
            'type' => 'incoming',
            'reference' => 'TRX011',
            'transaction_date' => now()->format('Y-m-d H:i:s'),
            // No items
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/transactions', $transactionData);

        $transaction = $response->json('data');

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/transactions/{$transaction['id']}");

        $response->assertStatus(200);

        $data = $response->json('data');

        // Empty transaction should have zero totals
        $this->assertEquals(0, $data['total_quantity']);
        $this->assertEquals(0, $data['total_value']);
        $this->assertEmpty($data['items']);
    }
}
