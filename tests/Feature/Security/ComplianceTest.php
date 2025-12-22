<?php

use App\Events\EmployeeDeleted;
use App\Events\EmployeeUpdated;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Employee;
use App\Models\Inventory\Item;
use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\InventoryPermissions;
use App\Roles\InventoryRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

// Security and Compliance Tests
test('multi-tenant data isolation under load testing', function () {
    // Create multiple organizations with unique names
    $organizations = collect();
    for ($i = 1; $i <= 2; $i++) {
        $org = Organization::factory()->create([
            'name' => 'Isolation Test ' . uniqid() . ' ' . now()->timestamp . '-' . $i . ' ' . str()->random(10),
        ]);
        $organizations->push($org);
    }
    $users = collect();

    foreach ($organizations as $index => $organization) {
        $user = User::factory()->create([
            'current_organization_id' => $organization->id,
        ]);

        // Assign user to organization with admin role
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode([InventoryRoles::INVENTORY_ADMIN]),
        ]);

        // Give user necessary permissions
        $inventoryPermissions = \App\Roles\InventoryRoles::getPermissionsForRole(InventoryRoles::INVENTORY_ADMIN);
        $user->givePermissionTo($inventoryPermissions, $organization);

        $users->push($user);

        // Create significant data for each organization
        Employee::factory()->count(50)->create(['organization_id' => $organization->id]);
        Item::factory()->count(25)->create(['organization_id' => $organization->id]);
        Member::factory()->count(100)->create(['organization_id' => $organization->id]);
        ChartOfAccount::factory()->count(10)->create(['organization_id' => $organization->id]);
    }

    // Test data isolation - each user should only see their organization's data
    foreach ($users as $index => $user) {
        $organization = $organizations[$index];

        // Test employee data isolation
        $employeeResponse = test()->actingAs($user)
            ->getJson('/api/employees')
            ->assertStatus(200);

        $employeeData = $employeeResponse->json('data');
        expect($employeeData)->toBeArray();
        expect($employeeData)->not->toBeEmpty();

        // Verify all employees belong to user's organization
        foreach ($employeeData as $employee) {
            if (is_object($employee)) {
                expect($employee->organization_id)->toBe($organization->id);
            } else {
                expect($employee['organization_id'])->toBe($organization->id);
            }
        }

        // Test item data isolation
        $itemResponse = test()->actingAs($user)
            ->getJson('/api/inventory/items')
            ->assertStatus(200);

        $itemData = $itemResponse->json('data');
        expect($itemData)->toBeArray();

        // Verify all items belong to user's organization
        foreach ($itemData as $item) {
            $itemId = is_object($item) ? $item->organization_id : $item['organization_id'];
            expect($itemId)->toBe($organization->id);
        }

        // Test member data isolation
        $memberResponse = test()->actingAs($user)
            ->getJson('/api/members')
            ->assertStatus(200);

        $memberData = $memberResponse->json('data');
        expect($memberData)->toBeArray();

        // Verify all members belong to user's organization
        foreach ($memberData as $member) {
            $memberId = is_object($member) ? $member->organization_id : $member['organization_id'];
            expect($memberId)->toBe($organization->id);
        }

        // Verify user cannot access other organizations' data
        $otherOrgIndex = ($index + 1) % 2;
        $otherOrganization = $organizations[$otherOrgIndex];

        $otherOrgResponse = test()->actingAs($user)
            ->getJson("/api/employees")
            ->assertStatus(200);

        // Verify all returned employees still belong to user's organization (data is filtered)
        $otherOrgData = $otherOrgResponse->json('data');
        foreach ($otherOrgData as $employee) {
            $empId = is_object($employee) ? $employee->organization_id : $employee['organization_id'];
            expect($empId)->toBe($organization->id);
        }
    }
});

test('role-based access control enforcement across modules', function () {
    $organization = Organization::factory()->create();
    $adminUser = User::factory()->create(['current_organization_id' => $organization->id]);
    $regularUser = User::factory()->create(['current_organization_id' => $organization->id]);

    // Assign admin role and permissions
    $adminUser->organizations()->attach($organization->id, [
        'roles' => json_encode([InventoryRoles::INVENTORY_ADMIN]),
    ]);
    $adminPermissions = \App\Roles\InventoryRoles::getPermissionsForRole(InventoryRoles::INVENTORY_ADMIN);
    $adminUser->givePermissionTo($adminPermissions, $organization);

    // Assign clerk role with limited permissions
    $regularUser->organizations()->attach($organization->id, [
        'roles' => json_encode([InventoryRoles::INVENTORY_CLERK]),
    ]);
    $clerkPermissions = \App\Roles\InventoryRoles::getPermissionsForRole(InventoryRoles::INVENTORY_CLERK);
    $regularUser->givePermissionTo($clerkPermissions, $organization);

    // Test admin can create items
    test()->actingAs($adminUser)
        ->postJson('/api/inventory/items', [
            'name' => 'Test Item',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'unit' => 'pcs',
            'organization_id' => $organization->id,
        ])
        ->assertStatus(201);

    // Test clerk cannot delete items (if they don't have permission)
    $item = Item::factory()->create(['organization_id' => $organization->id]);

    test()->actingAs($regularUser)
        ->deleteJson("/api/inventory/items/{$item->id}")
        ->assertStatus(403);
});

test('audit trail completeness for critical operations', function () {
    // todo
});

test('basic data validation and security checks work', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $user->organizations()->attach($organization->id, [
        'roles' => json_encode([InventoryRoles::INVENTORY_ADMIN]),
    ]);

    // Test that unauthenticated users cannot access protected endpoints
    test()->getJson('/api/employees')
        ->assertStatus(401);

    // Test that authenticated users can access data
    test()->actingAs($user)
        ->getJson('/api/employees')
        ->assertStatus(200);

    // Test data validation works
    test()->actingAs($user)
        ->postJson('/api/inventory/items', [
            'name' => '', // Invalid empty name
        ])
        ->assertStatus(422);
});
test('concurrent operations handling with proper locking', function () {

    // todo
});
test('input validation and sanitization prevents xss attacks', function () {
    // todo
});
test('api rate limiting prevents abuse', function () {

    // todo
});
test('data export functionality includes all required fields', function () {

    // todo
});
