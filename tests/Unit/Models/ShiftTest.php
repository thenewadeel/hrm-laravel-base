<?php

use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a shift', function () {
    $shift = Shift::factory()->create();

    expect($shift)->toBeInstanceOf(Shift::class)
        ->and($shift->name)->toBeString()
        ->and($shift->code)->toBeString();
});

it('calculates working hours correctly', function () {
    $shift = Shift::factory()->create([
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    expect($shift->duration)->toBe(8.0);
});

it('handles overnight shifts', function () {
    $shift = Shift::factory()->create([
        'start_time' => '22:00',
        'end_time' => '06:00',
    ]);

    expect($shift->duration)->toBe(8.0);
});

it('has many employees', function () {
    $shift = Shift::factory()->create();
    $employees = \App\Models\Employee::factory()->count(2)->create([
        'shift_id' => $shift->id,
    ]);

    expect($shift->employees)->toHaveCount(2);
});

it('has active scope', function () {
    Shift::factory()->count(2)->create(['is_active' => true]);
    Shift::factory()->count(1)->create(['is_active' => false]);

    $activeShifts = Shift::active()->get();

    expect($activeShifts)->toHaveCount(2);
});

it('validates days of week format', function () {
    $shift = Shift::factory()->create([
        'days_of_week' => ['monday', 'wednesday', 'friday'],
    ]);

    expect($shift->days_of_week)->toBeArray()
        ->and($shift->days_of_week)->toContain('monday');
});
