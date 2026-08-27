<?php

use App\Models\OrganizationUnit;
use Tests\Traits\SetupOrganization;

uses(SetupOrganization::class);

beforeEach(function () {
    $this->setupOrganization();
});

it('lists departments for the current organization', function () {
    OrganizationUnit::factory()->create([
        'organization_id' => $this->organization->id,
        'name' => 'Engineering',
        'type' => 'department',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('organization.units.index'));

    $response->assertStatus(200);
    $response->assertSee('Engineering');
    $response->assertSee('Department Management');
});

it('shows the create department form', function () {
    $response = $this->actingAs($this->user)
        ->get(route('organization.units.create'));

    $response->assertStatus(200);
    $response->assertSee('Add Department');
    $response->assertSee('name');
    $response->assertSee('type');
});

it('creates a new department', function () {
    $response = $this->actingAs($this->user)
        ->post(route('organization.units.store'), [
            'name' => 'Sales',
            'type' => 'department',
            'parent_id' => $this->organizationUnit->id,
        ]);

    $response->assertRedirect(route('organization.units.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('organization_units', [
        'name' => 'Sales',
        'type' => 'department',
        'parent_id' => $this->organizationUnit->id,
        'organization_id' => $this->organization->id,
    ]);
});

it('validates required fields when creating a department', function () {
    $response = $this->actingAs($this->user)
        ->post(route('organization.units.store'), [
            'name' => '',
            'type' => '',
        ]);

    $response->assertSessionHasErrors(['name', 'type']);
});

it('updates an existing department', function () {
    $unit = OrganizationUnit::factory()->create([
        'organization_id' => $this->organization->id,
        'name' => 'Old Name',
        'type' => 'department',
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('organization.units.update', $unit), [
            'name' => 'New Name',
            'type' => 'division',
            'parent_id' => $this->organizationUnit->id,
        ]);

    $response->assertRedirect(route('organization.units.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('organization_units', [
        'id' => $unit->id,
        'name' => 'New Name',
        'type' => 'division',
        'organization_id' => $this->organization->id,
    ]);
});

it('does not allow editing a department from another organization', function () {
    $otherOrgUnit = OrganizationUnit::factory()->create([
        'name' => 'Other Org Unit',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('organization.units.edit', $otherOrgUnit));

    $response->assertNotFound();
});

it('deletes a department', function () {
    $unit = OrganizationUnit::factory()->create([
        'organization_id' => $this->organization->id,
        'name' => 'Temp Dept',
        'type' => 'department',
    ]);

    $response = $this->actingAs($this->user)
        ->delete(route('organization.units.destroy', $unit));

    $response->assertRedirect(route('organization.units.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('organization_units', [
        'id' => $unit->id,
    ]);
});
