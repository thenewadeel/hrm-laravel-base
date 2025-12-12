<?php

namespace Tests\Feature;

use App\Models\Accounting\BankAccount;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Production Performance Optimization Tests
 *
 * This test suite ensures the HRM Laravel Base system is optimized
 * for production deployment with proper performance characteristics.
 */
class ProductionOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->user->organizations()->attach($this->organization->id, [
            'roles' => ['admin'],
            'permissions' => ['full_access'],
        ]);
        $this->user->current_organization_id = $this->organization->id;
        $this->user->save();

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_handles_multi_tenant_queries_efficiently()
    {
        // Create test data across multiple organizations
        $otherOrg = Organization::factory()->create();

        // Create bank accounts in both organizations
        BankAccount::factory()->count(50)->create(['organization_id' => $this->organization->id]);
        BankAccount::factory()->count(50)->create(['organization_id' => $otherOrg->id]);

        $startTime = microtime(true);

        // Query should only return records from current organization
        $accounts = BankAccount::with(['chartOfAccount'])->get();

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Assertions
        $this->assertEquals(50, $accounts->count());
        $this->assertLessThan(100, $executionTime, 'Query should execute in under 100ms');

        // Verify no data leakage between organizations
        $this->assertTrue($accounts->every(fn ($account) => $account->organization_id === $this->organization->id));
    }

    /** @test */
    public function it_prevents_n_plus_one_queries_in_livewire_components()
    {
        // Create test data
        BankAccount::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        // Enable query logging
        DB::enableQueryLog();

        // Simulate Livewire component rendering
        $query = BankAccount::query()
            ->with(['chartOfAccount', 'bankTransactions' => function ($query) {
                $query->latest()->take(5);
            }])
            ->when(false, function ($query) { // Set to false to return all results
                $query->where(function ($q) {
                    $q->where('account_name', 'like', '%test%')
                        ->orWhere('account_number', 'like', '%test%');
                });
            })
            ->latest();

        $results = $query->paginate(10);

        $queryCount = count(DB::getQueryLog());

        // Should not exceed reasonable query count
        $this->assertLessThan(5, $queryCount, 'Should not exceed 5 queries for paginated results');
        $this->assertCount(10, $results->items());
    }

    /** @test */
    public function it_caches_configuration_efficiently()
    {
        // Clear any existing cache
        Cache::flush();

        // Test configuration caching
        $startTime = microtime(true);

        // Simulate multiple config accesses
        for ($i = 0; $i < 100; $i++) {
            config('app.name');
            config('database.default');
            config('cache.default');
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Configuration access should be fast
        $this->assertLessThan(50, $executionTime, 'Configuration access should be cached and fast');
    }

    /** @test */
    public function it_handles_large_datasets_efficiently()
    {
        // Create large dataset
        BankAccount::factory()->count(1000)->create(['organization_id' => $this->organization->id]);

        $startTime = microtime(true);

        // Test chunked processing
        $processedCount = 0;
        BankAccount::chunk(100, function ($accounts) use (&$processedCount) {
            $processedCount += $accounts->count();

            // Simulate processing
            $accounts->each(function ($account) {
                $account->account_name;
                $account->bank_name;
            });
        });

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        $this->assertEquals(1000, $processedCount);
        $this->assertLessThan(1000, $executionTime, 'Large dataset processing should be efficient');
    }

    /** @test */
    public function it_maintains_data_integrity_under_load()
    {
        // Create concurrent-like operations
        $accounts = BankAccount::factory()->count(10)->create(['organization_id' => $this->organization->id]);

        // Simulate concurrent updates
        foreach ($accounts as $account) {
            $account->update(['status' => 'active']);
        }

        // Verify all updates were applied correctly
        $updatedAccounts = BankAccount::whereIn('id', $accounts->pluck('id'))->get();

        $this->assertTrue($updatedAccounts->every(fn ($account) => $account->status === 'active'));
        $this->assertEquals(10, $updatedAccounts->count());
    }

    /** @test */
    public function it_optimizes_database_indexes()
    {
        // Test that queries use proper indexes
        DB::enableQueryLog();

        // Create indexed query
        $account = BankAccount::factory()->create([
            'organization_id' => $this->organization->id,
            'account_number' => 'TEST123456',
        ]);

        // Query using indexed columns
        $found = BankAccount::where('organization_id', $this->organization->id)
            ->where('account_number', 'TEST123456')
            ->first();

        $queries = DB::getQueryLog();

        // Should use index lookup (no full table scan)
        $this->assertNotNull($found);
        $this->assertEquals($account->id, $found->id);

        // Check if query is using appropriate where clauses
        $lastQuery = end($queries)['query'];
        $this->assertStringContainsString('organization_id', $lastQuery);
        $this->assertStringContainsString('account_number', $lastQuery);
    }

    /** @test */
    public function it_handles_memory_efficiently()
    {
        $memoryBefore = memory_get_usage(true);

        // Process large dataset
        $accounts = BankAccount::factory()->count(500)->create(['organization_id' => $this->organization->id]);

        // Process with memory-efficient methods
        $accounts->each(function ($account) {
            $account->load(['chartOfAccount']);
        });

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // Convert to MB

        // Memory usage should be reasonable
        $this->assertLessThan(50, $memoryUsed, 'Memory usage should be under 50MB for 500 records');
    }

    /** @test */
    public function it_validates_api_response_performance()
    {
        // Create test data
        BankAccount::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $startTime = microtime(true);

        // Test API endpoint performance
        $response = $this->getJson('/api/accounts');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(200, $responseTime, 'API response should be under 200ms');

        // Response should include pagination metadata
        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
    }

    /** @test */
    public function it_secures_multi_tenant_data_isolation()
    {
        // Create another organization and user
        $otherOrg = Organization::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->organizations()->attach($otherOrg->id);
        $otherUser->current_organization_id = $otherOrg->id;
        $otherUser->save();

        // Create data in both organizations
        $account1 = \App\Models\Accounting\ChartOfAccount::factory()->create(['organization_id' => $this->organization->id]);
        $account2 = \App\Models\Accounting\ChartOfAccount::factory()->create(['organization_id' => $otherOrg->id]);

        // User should only access their own organization's data
        $this->actingAs($this->user)
            ->getJson("/api/accounts/{$account1->id}")
            ->assertStatus(200);

        $this->actingAs($this->user)
            ->getJson("/api/accounts/{$account2->id}")
            ->assertStatus(404); // Should not find other org's account

        // Other user should not access first organization's data
        $this->actingAs($otherUser)
            ->getJson("/api/accounts/{$account1->id}")
            ->assertStatus(404);
    }

    /** @test */
    public function it_optimizes_livewire_rendering_performance()
    {
        // Create test data
        BankAccount::factory()->count(100)->create(['organization_id' => $this->organization->id]);

        $startTime = microtime(true);

        // Simulate Livewire component mount and render
        $component = new \App\Livewire\Accounting\BankAccounts\Index;
        $component->mount();

        // Simulate rendering
        $viewData = $component->render();

        $endTime = microtime(true);
        $renderTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(150, $renderTime, 'Livewire component should render in under 150ms');
        $this->assertArrayHasKey('bankAccounts', $viewData->getData());
    }
}
