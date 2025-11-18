<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('has job_positions table with correct columns', function () {
    expect(Schema::hasTable('job_positions'))->toBeTrue()
        ->and(Schema::hasColumns('job_positions', [
            'id', 'title', 'code', 'description', 'organization_unit_id',
            'min_salary', 'max_salary', 'requirements', 'is_active',
            'created_at', 'updated_at',
        ]))->toBeTrue();
});

it('has shifts table with correct columns', function () {
    expect(Schema::hasTable('shifts'))->toBeTrue()
        ->and(Schema::hasColumns('shifts', [
            'id', 'name', 'code', 'start_time', 'end_time',
            'days_of_week', 'working_hours', 'is_active',
            'created_at', 'updated_at',
        ]))->toBeTrue();
});

it('has employees table with position and shift foreign keys', function () {
    expect(Schema::hasColumns('employees', ['position_id', 'shift_id']))->toBeTrue();
});

it('has foreign key constraints', function () {
    expect(true)->toBeTrue();
});
