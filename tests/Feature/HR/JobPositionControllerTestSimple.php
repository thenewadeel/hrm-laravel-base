<?php

use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a job position without authorization', function () {
    $user = User::factory()->create();
    $orgUnit = OrganizationUnit::factory()->create();

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
