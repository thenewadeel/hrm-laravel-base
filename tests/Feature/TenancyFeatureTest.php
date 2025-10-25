<?php

namespace Tests\Feature;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\OrganizationUnit;
use App\Models\Scopes\OrganizationScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Tests\Traits\TenancyTestSetup;
use PHPUnit\Framework\Attributes\Test;

/**
 * A generic feature test to validate the integrity of the Organization Global Scope
 * and BelongsToOrganization trait across all tenant-scoped models.
 */
class TenancyFeatureTest extends TestCase
{
    use RefreshDatabase, TenancyTestSetup;

    /**
     * Implement the abstract method required by the TenancyTestSetup trait.
     * This method MUST return the array of models to be tested.
     *
     * @return array<class-string<\Illuminate\Database\Eloquent\Model>>
     */
    protected function tenantModels(): array
    {
        return [
            ChartOfAccount::class,
            JournalEntry::class,
            // Store::class,
            OrganizationUnit::class,
            Item::class,            // Add other models here:
            // \App\Models\Invoice::class,
            // \App\Models\Customer::class,
        ];
    }

    /**
     * Set up the testing environment and tenant data.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Run migrations from the custom path for testing
        Artisan::call('migrate', [
            // '--database' => 'sqlite', // Or your desired test database connection
            '--path' => 'database/migrations/inventory',
            // '--force' => true, // Essential for production environments, though not strictly needed for in-memory SQLite
        ]);
        // Call the trait's setup method
        $this->setUpTenancy();
    }

    /**
     * Test 1: Ensures queries are automatically scoped (Isolation Test).
     */
    #[Test]
    public function test_queries_are_automatically_scoped_to_the_authenticated_users_organization(): void
    {
        $this->actingAs($this->userA); // Log in as User A (Org A)

        foreach ($this->tenantModels() as $modelClass) {
            // Retrieve all records for the current model
            $records = $modelClass::all();

            // Assertion 1: Must only see the 2 records belonging to Org A
            $this->assertCount(2, $records, "Model $modelClass failed Isolation (Wrong Count).");

            // Assertion 2: All returned records must belong to Org A
            $this->assertTrue(
                $records->every(fn($record) => $record->organization_id === $this->orgA->id),
                "Model $modelClass failed Isolation (Contains Foreign Data)."
            );
        }
    }

    /**
     * Test 2: Ensures the organization_id is automatically set on creation (Auto-Assignment Test).
     */
    #[Test]
    public function test_organization_id_is_automatically_set_on_creation(): void
    {
        $this->actingAs($this->userB); // Log in as User B (Org B)

        foreach ($this->tenantModels() as $modelClass) {
            // Create a new record *without* explicitly passing organization_id
            $newRecord = $modelClass::factory()->create([
                // Overriding organization_id to null will force the `creating` event listener to run
                'organization_id' => null,
            ]);

            // Assertion: organization_id must match the authenticated user's organization
            $this->assertEquals(
                $this->orgB->id,
                $newRecord->organization_id,
                "Model $modelClass failed Auto-Assignment (ID not set)."
            );

            // Clean up the created record for idempotency
            $newRecord->delete();
        }
    }

    /**
     * Test 3: Ensures the global scope can be deliberately bypassed (Bypass Test).
     */
    #[Test]
    public function test_global_scope_can_be_bypassed_for_admin_queries(): void
    {
        // Login is not strictly necessary but keeps the context clear
        $this->actingAs($this->userA);

        foreach ($this->tenantModels() as $modelClass) {
            // Retrieve all records using withoutGlobalScope()
            $allRecords = $modelClass::withoutGlobalScope(OrganizationScope::class)->get();

            // Assertion: Must see all 3 records (2 from Org A, 1 from Org B)
            $this->assertCount(
                3,
                $allRecords,
                "Model $modelClass failed Bypass Test (Expected 3 records total)."
            );
        }
    }
}
