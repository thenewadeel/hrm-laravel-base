<?php

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects a job position with a department from another organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $otherOrgUnit = OrganizationUnit::factory()->create();

    $data = [
        'title' => 'Software Engineer',
        'code' => 'SE001',
        'organization_unit_id' => $otherOrgUnit->id,
        'description' => 'Develops software',
        'min_salary' => 50000,
        'max_salary' => 80000,
    ];

    $response = $this->actingAs($user)->post(route('hr.positions.store'), $data);

    $response->assertSessionHasErrors('organization_unit_id');
    $this->assertDatabaseMissing('job_positions', ['code' => 'SE001']);
});

it('creates a job position with a department in the same organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $orgUnit = OrganizationUnit::factory()->create(['organization_id' => $organization->id]);

    $data = [
        'title' => 'Software Engineer',
        'code' => 'SE001',
        'organization_unit_id' => $orgUnit->id,
        'description' => 'Develops software',
        'min_salary' => 50000,
        'max_salary' => 80000,
    ];

    $response = $this->actingAs($user)->post(route('hr.positions.store'), $data);

    $response->assertRedirect()
        ->assertSessionHas('success');
    $this->assertDatabaseHas('job_positions', $data);
});
