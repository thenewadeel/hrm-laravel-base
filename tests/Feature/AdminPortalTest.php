<?php

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Roles\InventoryRoles;

it('can access admin dashboard with admin role', function () {
    // Create admin user
    $admin = User::factory()->create(['email' => config('app.admin_email', 'admin@example.com')]);

    $organization = Organization::factory()->create();
    $admin->organizations()->attach($organization->id, [
        'roles' => json_encode([InventoryRoles::INVENTORY_ADMIN]),
        'organization_unit_id' => OrganizationUnit::factory()->create(['organization_id' => $organization->id])->id,
        'position' => 'Administrator',
    ]);
    $admin->current_organization_id = $organization->id;
    $admin->save();

    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $response->assertStatus(200);
    $response->assertSee('Admin Portal');
});

it('cannot access admin dashboard without admin role', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/dashboard');
    $response->assertStatus(403);
});

it('can attach user to organization via admin portal', function () {
    // Create admin user
    $admin = User::factory()->create(['email' => config('app.admin_email', 'admin@example.com')]);

    $organization = Organization::factory()->create();
    $admin->organizations()->attach($organization->id, [
        'roles' => json_encode([InventoryRoles::INVENTORY_ADMIN]),
        'organization_unit_id' => OrganizationUnit::factory()->create(['organization_id' => $organization->id])->id,
        'position' => 'Administrator',
    ]);
    $admin->current_organization_id = $organization->id;
    $admin->save();

    // Create user without organization
    $user = User::factory()->create();
    $unit = OrganizationUnit::factory()->create(['organization_id' => $organization->id]);

    $response = $this->actingAs($admin)->post('/admin/attach-user', [
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'organization_unit_id' => $unit->id,
        'position' => 'Staff',
        'roles' => [InventoryRoles::INVENTORY_CLERK],
        'set_as_current' => true,
    ]);

    $response->assertRedirect('/admin/dashboard');
    $response->assertSessionHas('success');

    // Verify attachment
    $this->assertDatabaseHas('organization_user', [
        'user_id' => $user->id,
        'organization_id' => $organization->id,
        'organization_unit_id' => $unit->id,
        'position' => 'Staff',
    ]);

    // Verify current organization was set
    $user->refresh();
    $this->assertEquals($organization->id, $user->current_organization_id);
});
