<?php

namespace Tests\Feature\Inventory;

use Tests\Traits\SetupInventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Roles\InventoryRoles;

class PermissionTest extends TestCase
{
    use RefreshDatabase, SetupInventory;

    /** @test */
    public function inventory_admin_has_all_permissions()
    {
        $setup = $this->createUserWithInventoryPermissions();

        $this->actingAs($setup['user'])
            ->getJson('/api/inventory/stores')
            ->assertStatus(200);
        // dd('cp1');
        $this->actingAs($setup['user'])
            ->postJson('/api/inventory/stores', [
                'name' => 'Test Store',
                'code' => 'TEST001',
                'organization_id' => $setup['organization']->id,
                'organization_unit_id' => $setup['organization_unit']->id
            ])
            ->assertStatus(201);
    }

    /** @test */
    public function store_manager_cannot_delete_stores()
    {
        $setup = $this->createStoreManager();
        $store = $setup['store'];

        $this->actingAs($setup['user'])
            ->deleteJson("/api/inventory/stores/{$store->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function inventory_clerk_cannot_finalize_transactions()
    {
        $setup = $this->createInventoryClerk();
        $transactionSetup = $this->createDraftTransactionWithItems($setup['store'], $setup['user']);

        $this->actingAs($setup['user'])
            ->putJson("/api/inventory/transactions/{$transactionSetup['transaction']->id}/finalize")
            ->assertStatus(403);
    }

    /** @test */
    public function auditor_can_only_view_inventory()
    {
        $setup = $this->createAuditor();

        $this->actingAs($setup['user'])
            ->getJson('/api/inventory/stores')
            ->assertStatus(200);

        $this->actingAs($setup['user'])
            ->postJson('/api/inventory/stores', [])
            ->assertStatus(403);
    }

    /** @test */
    public function user_cannot_access_other_organization_inventory()
    {
        // Create user with permissions in Organization A
        $setupA = $this->createUserWithInventoryPermissions();

        // Create Organization B with its own store
        $setupB = $this->createInventorySetup();
        $storeB = $setupB['store'];

        // User from Org A tries to access Org B's store
        $this->actingAs($setupA['user'])
            ->getJson("/api/inventory/stores/{$storeB->id}")
            ->assertStatus(403);
    }
}
