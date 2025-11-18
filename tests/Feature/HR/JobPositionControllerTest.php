<?php

use App\Models\JobPosition;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays job positions index', function () {
    $user = User::factory()->create();
    JobPosition::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('hr.positions.index'));

    $response->assertStatus(200)
        ->assertViewHas('positions');
});

it('creates a job position', function () {
    $user = User::factory()->create();
    $organization = \App\Models\Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $data = [
        'title' => 'Software Engineer',
        'code' => 'SE001',
        'description' => 'Senior software engineer position',
        'organization_unit_id' => OrganizationUnit::factory()->create(['organization_id' => $organization->id])->id,
        'min_salary' => 80000,
        'max_salary' => 120000,
        'requirements' => ['PHP', 'Laravel', 'MySQL'],
        'is_active' => true,
    ];

    $response = $this->actingAs($user)->post(route('hr.positions.store'), $data);

    $response->assertRedirect()
        ->assertSessionHas('success');
    $this->assertDatabaseHas('job_positions', [
        'title' => 'Software Engineer',
        'code' => 'SE001',
        'description' => 'Senior software engineer position',
        'organization_unit_id' => 1,
        'min_salary' => 80000,
        'max_salary' => 120000,
        'requirements' => '["PHP","Laravel","MySQL"]',
        'is_active' => true
    ]);
});

it('validates required fields on create', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('hr.positions.store'), []);

    $response->assertSessionHasErrors(['title', 'code']);
});

it('updates a job position', function () {
    $user = User::factory()->create();
    $organization = \App\Models\Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $position = JobPosition::factory()->create(['organization_id' => $organization->id]);

    $data = [
        'title' => 'Updated Title',
        'code' => $position->code,
        'organization_unit_id' => $position->organization_unit_id,
    ];

    $response = $this->actingAs($user)->put(route('hr.positions.update', $position), $data);

    $response->assertRedirect()
        ->assertSessionHas('success');
    $this->assertDatabaseHas('job_positions', ['title' => 'Updated Title']);
});

it('deletes a job position', function () {
    $user = User::factory()->create();
    $organization = \App\Models\Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();
    
    $position = JobPosition::factory()->create(['organization_id' => $organization->id]);

    // Ensure position has no employees before deletion
    $this->assertEquals(0, $position->employees()->count());

    $response = $this->actingAs($user)->delete(route('hr.positions.destroy', $position));

    $response->assertRedirect()
        ->assertSessionHas('success');
    
    // Check that position was actually deleted from database
    $this->assertDatabaseMissing('job_positions', ['id' => $position->id]);
});

it('searches job positions', function () {
    $user = User::factory()->create();
    $organization = \App\Models\Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();
    
    JobPosition::factory()->create(['title' => 'Developer', 'organization_id' => $organization->id]);
    JobPosition::factory()->create(['title' => 'Manager', 'organization_id' => $organization->id]);

    $response = $this->actingAs($user)->get(route('hr.positions.index', ['search' => 'dev']));

    $response->assertStatus(200)
        ->assertViewHas('positions', function ($positions) {
            return $positions->count() === 1 && $positions->first()->title === 'Developer';
        });
});
