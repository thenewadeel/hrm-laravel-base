<?php

use App\Models\JobPosition;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a job position', function () {
    $orgUnit = OrganizationUnit::factory()->create();
    $position = JobPosition::factory()->create([
        'organization_unit_id' => $orgUnit->id,
    ]);

    expect($position)->toBeInstanceOf(JobPosition::class)
        ->and($position->title)->toBeString()
        ->and($position->code)->toBeString();
});

it('belongs to an organization unit', function () {
    $orgUnit = OrganizationUnit::factory()->create();
    $position = JobPosition::factory()->create([
        'organization_unit_id' => $orgUnit->id,
    ]);

    expect($position->organizationUnit)->toBeInstanceOf(OrganizationUnit::class)
        ->and($position->organizationUnit->id)->toBe($orgUnit->id);
});

it('has many employees', function () {
    $position = JobPosition::factory()->create();
    $employees = \App\Models\Employee::factory()->count(3)->create([
        'position_id' => $position->id,
    ]);

    expect($position->employees)->toHaveCount(3);
});

it('has active scope', function () {
    JobPosition::factory()->count(2)->create(['is_active' => true]);
    JobPosition::factory()->count(1)->create(['is_active' => false]);

    $activePositions = JobPosition::active()->get();

    expect($activePositions)->toHaveCount(2);
});

it('has search scope', function () {
    JobPosition::factory()->create(['title' => 'Developer']);
    JobPosition::factory()->create(['title' => 'Manager']);

    $results = JobPosition::search('dev')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->title)->toBe('Developer');
});
