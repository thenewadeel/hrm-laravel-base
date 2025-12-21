<?php

namespace Tests\Browser\E2E\Workflows;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\BaseBrowserTest;
use Tests\Browser\E2E\Concerns\HandlesE2ETestSetup;
use Tests\Browser\E2E\Concerns\HandlesWorkflowAssertions;
use Tests\Browser\E2E\Assertions\E2EAssertions;
use Tests\Browser\E2E\Fixtures\E2ETestFixtures;

class InventoryManagementAndStockTransactionsTest extends BaseBrowserTest
{
    use HandlesE2ETestSetup, HandlesWorkflowAssertions;

    protected $testData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = E2ETestFixtures::createOrganizationSetup();
    }

    protected function tearDown(): void
    {
        E2ETestFixtures::cleanup($this->testData);
        parent::tearDown();
    }

    /**
     * Test complete inventory management and stock transactions workflow.
     */
    public function test_complete_inventory_management_and_stock_transactions_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];
            $organization = $this->testData['organization'];

            // Step 1: Login as admin
            $browser->loginAs($admin)
                ->visit('/')
                ->waitForText($organization->name, 10);

            E2EAssertions::assertPageTitle($browser, 'Dashboard');
            E2EAssertions::assertDataIsolation($browser, $organization->id);

            // Step 2: Navigate to Inventory module
            $this->navigateToModule($browser, 'Inventory');
            $this->waitForPageLoad($browser);

            // Step 3: Create new inventory item
            $browser->clickLink('Add Item')
                ->waitFor('.item-form', 10)
                ->type('name', 'Wireless Mouse')
                ->type('sku', 'MOUSE-001')
                ->type('description', 'Ergonomic wireless mouse')
                ->select('category', 'Electronics')
                ->type('unit_price', 45.00)
                ->type('cost_price', 25.00)
                ->type('reorder_level', 20)
                ->type('min_stock_level', 5)
                ->select('unit_of_measure', 'pieces')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Item created successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'item_creation');

            // Step 4: Verify item is created
            $browser->assertSeeIn('.items-table', 'Wireless Mouse')
                ->assertSeeIn('.items-table', 'MOUSE-001')
                ->assertSeeIn('.items-table', '45.00')
                ->assertSeeIn('.items-table', 'Electronics');

            // Step 5: Process stock IN transaction
            $browser->clickLink('Stock Management')
                ->waitFor('.stock-management', 10)
                ->clickLink('Stock IN')
                ->waitFor('.stock-in-form', 10)
                ->select('store_id', $this->testData['stores']['main_store']->id)
                ->select('supplier_id', 1) // Assuming supplier exists
                ->type('reference_number', 'PO002')
                ->type('transaction_date', now()->format('Y-m-d'))
                ->type('notes', 'Purchase from Tech Supplier');

            // Add items to transaction
            $browser->click('button[data-action="add_item"]')
                ->waitFor('.transaction-item-row', 5)
                ->select('item_id_0', 5) // New wireless mouse
                ->type('quantity_0', 50)
                ->type('unit_cost_0', 25.00)
                ->click('button[data-action="add_item"]')
                ->waitFor('.transaction-item-row:nth-child(2)', 5)
                ->select('item_id_1', $this->testData['items']['laptop']->id)
                ->type('quantity_1', 10)
                ->type('unit_cost_1', 800.00)
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Stock IN transaction processed successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'stock_in_transaction');

            // Step 6: Verify stock levels updated
            $browser->clickLink('View Stock Levels')
                ->waitFor('.stock-levels', 10);

            E2EAssertions::assertInventoryStockLevel($browser, 'Wireless Mouse', 50);
            E2EAssertions::assertInventoryStockLevel($browser, 'Laptop Computer', 30); // 20 + 10

            // Step 7: Process stock OUT transaction (sale)
            $browser->clickLink('Stock Management')
                ->waitFor('.stock-management', 10)
                ->clickLink('Stock OUT')
                ->waitFor('.stock-out-form', 10)
                ->select('store_id', $this->testData['stores']['main_store']->id)
                ->select('customer_id', 1) // Assuming customer exists
                ->type('reference_number', 'SALE002')
                ->type('transaction_date', now()->format('Y-m-d'))
                ->type('notes', 'Sale to Customer XYZ');

            // Add items to transaction
            $browser->click('button[data-action="add_item"]')
                ->waitFor('.transaction-item-row', 5)
                ->select('item_id_0', 5) // Wireless mouse
                ->type('quantity_0', 15)
                ->type('unit_price_0', 45.00)
                ->click('button[data-action="add_item"]')
                ->waitFor('.transaction-item-row:nth-child(2)', 5)
                ->select('item_id_1', $this->testData['items']['laptop']->id)
                ->type('quantity_1', 5)
                ->type('unit_price_1', 1200.00)
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Stock OUT transaction processed successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'stock_out_transaction');

            // Step 8: Verify updated stock levels
            $browser->clickLink('View Stock Levels')
                ->waitFor('.stock-levels', 10);

            E2EAssertions::assertInventoryStockLevel($browser, 'Wireless Mouse', 35); // 50 - 15
            E2EAssertions::assertInventoryStockLevel($browser, 'Laptop Computer', 25); // 30 - 5

            // Step 9: Process stock TRANSFER between stores
            $browser->clickLink('Stock Management')
                ->waitFor('.stock-management', 10)
                ->clickLink('Stock Transfer')
                ->waitFor('.stock-transfer-form', 10)
                ->select('from_store_id', $this->testData['stores']['main_store']->id)
                ->select('to_store_id', $this->testData['stores']['warehouse']->id)
                ->type('reference_number', 'TRANSFER001')
                ->type('transaction_date', now()->format('Y-m-d'))
                ->type('notes', 'Transfer to warehouse');

            // Add items to transfer
            $browser->click('button[data-action="add_item"]')
                ->waitFor('.transaction-item-row', 5)
                ->select('item_id_0', $this->testData['items']['office_chair']->id)
                ->type('quantity_0', 10)
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Stock transfer processed successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'stock_transfer');

            // Step 10: Verify transfer results
            $browser->clickLink('View Stock Levels')
                ->waitFor('.stock-levels', 10)
                ->select('store_filter', $this->testData['stores']['main_store']->id)
                ->pause(500);

            E2EAssertions::assertInventoryStockLevel($browser, 'Office Chair', 15); // 25 - 10

            $browser->select('store_filter', $this->testData['stores']['warehouse']->id)
                ->pause(500);

            E2EAssertions::assertInventoryStockLevel($browser, 'Office Chair', 10); // 0 + 10

            // Step 11: Process stock ADJUSTMENT
            $browser->clickLink('Stock Management')
                ->waitFor('.stock-management', 10)
                ->clickLink('Stock Adjustment')
                ->waitFor('.stock-adjustment-form', 10)
                ->select('store_id', $this->testData['stores']['main_store']->id)
                ->type('reference_number', 'ADJUST001')
                ->type('transaction_date', now()->format('Y-m-d'))
                ->type('notes', 'Physical count adjustment');

            // Add adjustment items
            $browser->click('button[data-action="add_item"]')
                ->waitFor('.transaction-item-row', 5)
                ->select('item_id_0', $this->testData['items']['printer_paper']->id)
                ->type('quantity_0', 5)
                ->select('adjustment_type_0', 'increase')
                ->type('reason_0', 'Found missing stock')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Stock adjustment processed successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'stock_adjustment');

            // Step 12: Generate inventory valuation report
            $browser->clickLink('Reports')
                ->waitFor('.inventory-reports', 10)
                ->select('report_type', 'valuation')
                ->select('valuation_method', 'fifo')
                ->click('button[data-action="generate_report"]')
                ->waitFor('.report-content', 15);

            $browser->assertSee('Inventory Valuation Report')
                ->assertSee('FIFO Method')
                ->assertSeeIn('[data-total-items-value]', number_format($this->calculateInventoryValue(), 2));

            E2EAssertions::assertExportGenerated($browser, 'excel');

            // Step 13: Generate stock movement report
            $browser->select('report_type', 'stock_movement')
                ->type('date_from', now()->subDays(7)->format('Y-m-d'))
                ->type('date_to', now()->format('Y-m-d'))
                ->click('button[data-action="generate_report"]')
                ->waitFor('.report-content', 15);

            $browser->assertSee('Stock Movement Report')
                ->assertSee('Wireless Mouse')
                ->assertSee('Laptop Computer')
                ->assertSeeIn('[data-total-transactions]', '4'); // IN, OUT, TRANSFER, ADJUSTMENT

            // Step 14: Check reorder level alerts
            $browser->clickLink('Dashboard')
                ->waitFor('.inventory-dashboard', 10);

            // Create low stock scenario
            $browser->clickLink('Stock Management')
                ->waitFor('.stock-management', 10)
                ->clickLink('Stock OUT')
                ->waitFor('.stock-out-form', 10)
                ->select('store_id', $this->testData['stores']['main_store']->id)
                ->type('reference_number', 'SALE003')
                ->type('transaction_date', now()->format('Y-m-d'));

            $browser->click('button[data-action="add_item"]')
                ->waitFor('.transaction-item-row', 5)
                ->select('item_id_0', 5) // Wireless mouse
                ->type('quantity_0', 20) // This will bring stock to 15 (below reorder of 20)
                ->type('unit_price_0', 45.00)
                ->click('button[type="submit"]');

            // Check for reorder alert
            $browser->assertPresent('[data-reorder-alert]')
                ->assertSeeIn('[data-reorder-alert]', 'Wireless Mouse')
                ->assertSeeIn('[data-reorder-alert]', 'below reorder level');

            // Step 15: Test inventory transactions audit trail
            E2EAssertions::assertInventoryTransaction($browser, [
                'reference' => 'PO002',
                'type' => 'IN',
                'quantity' => '60', // 50 + 10
                'item_name' => 'Multiple Items',
            ]);

            E2EAssertions::assertInventoryTransaction($browser, [
                'reference' => 'SALE002',
                'type' => 'OUT',
                'quantity' => '20', // 15 + 5
                'item_name' => 'Multiple Items',
            ]);

            // Step 16: Test batch operations
            $browser->clickLink('Items Management')
                ->waitFor('.items-management', 10)
                ->check('batch_select[]', 0) // Select wireless mouse
                ->check('batch_select[]', 1) // Select laptop
                ->select('batch_action', 'update_price')
                ->waitFor('.batch-update-form', 5)
                ->type('price_increase_percentage', 10)
                ->click('button[data-action="execute_batch"]')
                ->waitFor('.batch-result', 10);

            E2EAssertions::assertSuccessMessage($browser, 'Batch price update completed successfully');

            // Verify price updates
            $browser->assertSeeIn('[data-item-price="Wireless Mouse"]', '49.50'); // 45 + 10%
            $browser->assertSeeIn('[data-item-price="Laptop Computer"]', '1,320.00'); // 1200 + 10%

            // Step 17: Test inventory forecasting
            $browser->clickLink('Forecasting')
                ->waitFor('.inventory-forecasting', 10)
                ->select('forecast_period', '3_months')
                ->select('forecast_method', 'moving_average')
                ->click('button[data-action="generate_forecast"]')
                ->waitFor('.forecast-results', 15);

            $browser->assertSee('Inventory Forecast')
                ->assertSeeIn('[data-forecast-period]', '3 Months')
                ->assertPresent('[data-forecast-chart]');

            // Step 18: Test responsive design
            $this->assertResponsiveDesign($browser, function (Browser $browser, string $viewport) {
                $browser->visit('/inventory/items')
                    ->waitFor('.items-table', 10)
                    ->assertPresent('.items-table');

                if ($viewport === 'mobile') {
                    $browser->assertPresent('.mobile-item-card')
                        ->assertMissing('.desktop-item-table');
                } else {
                    $browser->assertPresent('.desktop-item-table');
                }
            });

            // Step 19: Test search and filtering
            $browser->visit('/inventory/items')
                ->waitFor('.items-table', 10);

            E2EAssertions::assertSearchResults($browser, 'Wireless Mouse', ['Wireless Mouse', 'MOUSE-001']);

            $browser->select('filter_category', 'Electronics')
                ->pause(500)
                ->assertSeeIn('.items-table', 'Wireless Mouse')
                ->assertSeeIn('.items-table', 'Laptop Computer')
                ->assertDontSeeIn('.items-table', 'Office Chair'); // Furniture

            $browser->select('filter_stock_status', 'low_stock')
                ->pause(500)
                ->assertSeeIn('.items-table', 'Wireless Mouse'); // Below reorder level

            // Step 20: Test error handling
            $this->assertErrorHandling($browser, 'invalid_data', function (Browser $browser) {
                $browser->visit('/inventory/items/create')
                    ->waitFor('.item-form', 10)
                    ->type('unit_price', 'invalid-price')
                    ->type('cost_price', 'invalid-cost')
                    ->click('button[type="submit"]')
                    ->waitFor('.error-message', 5);
            });

            // Step 21: Test insufficient stock scenario
            $this->assertErrorHandling($browser, 'insufficient_stock', function (Browser $browser) {
                $browser->visit('/inventory/transactions/stock-out/create')
                    ->waitFor('.stock-out-form', 10)
                    ->select('store_id', $this->testData['stores']['main_store']->id)
                    ->click('button[data-action="add_item"]')
                    ->waitFor('.transaction-item-row', 5)
                    ->select('item_id_0', 5) // Wireless mouse
                    ->type('quantity_0', 100) // More than available (15)
                    ->type('unit_price_0', 45.00)
                    ->click('button[type="submit"]')
                    ->waitFor('.error-message', 5);
            });

            // Step 22: Verify data persistence
            $this->assertDataPersistence($browser, [
                '[data-total-items]' => '5', // Original 4 + 1 new
                '[data-total-stock-value]' => number_format($this->calculateInventoryValue(), 2),
                '[data-low-stock-items]' => '1', // Wireless mouse
            ]);

            // Step 23: Test performance
            $this->assertPageLoadPerformance($browser, 3000);

            // Step 24: Test accessibility
            E2EAssertions::assertAccessibilityFeatures($browser);

            // Final verification
            E2EAssertions::assertWorkflowCompletion($browser, 'complete_inventory_management', true);
        });
    }

    /**
     * Calculate total inventory value.
     */
    private function calculateInventoryValue(): float
    {
        // This would be calculated based on current stock levels and costs
        // For testing purposes, return a reasonable estimate
        return 125000.00;
    }

    /**
     * Test inventory management with multiple stores.
     */
    public function test_multi_store_inventory_management(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];

            $browser->loginAs($admin)
                ->visit('/inventory/stores')
                ->waitFor('.stores-management', 10);

            // Test store switching
            $browser->select('store_selector', $this->testData['stores']['main_store']->id)
                ->pause(500)
                ->assertSeeIn('[data-store-name]', 'Main Store');

            $browser->select('store_selector', $this->testData['stores']['warehouse']->id)
                ->pause(500)
                ->assertSeeIn('[data-store-name]', 'Main Warehouse');

            // Test inter-store transfer visibility
            $browser->visit('/inventory/transfers')
                ->waitFor('.transfers-table', 10)
                ->assertSeeIn('.transfers-table', 'TRANSFER001')
                ->assertSeeIn('.transfers-table', 'Main Store')
                ->assertSeeIn('.transfers-table', 'Main Warehouse');
        });
    }

    /**
     * Test inventory costing methods.
     */
    public function test_inventory_costing_methods(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];

            $browser->loginAs($admin)
                ->visit('/inventory/reports/costing')
                ->waitFor('.costing-reports', 10);

            // Test FIFO costing
            $browser->select('costing_method', 'fifo')
                ->click('button[data-action="calculate_costing"]')
                ->waitFor('.costing-results', 10)
                ->assertSeeIn('[data-costing-method]', 'FIFO')
                ->assertSeeIn('[data-total-value]', number_format($this->calculateFIFOValue(), 2));

            // Test Weighted Average costing
            $browser->select('costing_method', 'weighted_average')
                ->click('button[data-action="calculate_costing"]')
                ->waitFor('.costing-results', 10)
                ->assertSeeIn('[data-costing-method]', 'Weighted Average')
                ->assertSeeIn('[data-total-value]', number_format($this->calculateWeightedAverageValue(), 2));
        });
    }

    /**
     * Calculate FIFO inventory value.
     */
    private function calculateFIFOValue(): float
    {
        return 120000.00;
    }

    /**
     * Calculate Weighted Average inventory value.
     */
    private function calculateWeightedAverageValue(): float
    {
        return 118500.00;
    }

    /**
     * Test inventory cycle counting.
     */
    public function test_inventory_cycle_counting(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];

            $browser->loginAs($admin)
                ->visit('/inventory/cycle-count')
                ->waitFor('.cycle-count-management', 10)
                ->clickLink('Start Cycle Count')
                ->waitFor('.cycle-count-form', 5)
                ->select('store_id', $this->testData['stores']['main_store']->id)
                ->select('count_type', 'partial')
                ->check('category_ids[]', 1) // Electronics
                ->type('scheduled_date', now()->addDays(7)->format('Y-m-d'))
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Cycle count scheduled successfully');

            // Execute cycle count
            $browser->clickLink('Execute Count')
                ->waitFor('.cycle-count-execution', 10)
                ->assertSee('Wireless Mouse')
                ->assertSee('Laptop Computer')
                ->type('counted_quantity_0', 35) // Wireless mouse
                ->type('counted_quantity_1', 25) // Laptop
                ->click('button[data-action="complete_count"]');

            E2EAssertions::assertSuccessMessage($browser, 'Cycle count completed successfully');

            // Verify variance report
            $browser->assertSeeIn('[data-variance-report]', 'Variance Analysis')
                ->assertSeeIn('[data-total-variance]', '0'); // No variance in this case
        });
    }

    /**
     * Test inventory permissions and access control.
     */
    public function test_inventory_access_control(): void
    {
        $this->browse(function (Browser $browser) {
            $manager = $this->testData['users']['manager'];
            $member = $this->testData['users']['member'];

            // Test manager access
            $browser->loginAs($manager)
                ->visit('/inventory/items')
                ->waitFor('.items-table', 10);

            E2EAssertions::assertUserPermissions($browser, 
                ['view_items', 'view_stock_levels'], 
                ['delete_items', 'manage_stores']
            );

            // Test member access (should be restricted)
            $browser->loginAs($member)
                ->visit('/inventory/items')
                ->assertForbidden();

            // Test store-specific access
            $browser->loginAs($manager)
                ->visit('/inventory/stores')
                ->waitFor('.stores-management', 10);

            // Manager should only see assigned stores
            $browser->assertSee($this->testData['stores']['main_store']->name)
                ->assertDontSee($this->testData['stores']['secondary_store']->name);
        });
    }
}