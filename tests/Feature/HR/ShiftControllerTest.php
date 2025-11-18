<?php

use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays shifts index', function () {
    $user = User::factory()->create();
    Shift::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('hr.shifts.index'));

    $response->assertStatus(200)
        ->assertViewHas('shifts');
});

it('creates a shift', function () {
    $user = User::factory()->create();
    $organization = \App\Models\Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $data = [
        'name' => 'Morning Shift',
        'code' => 'MS001',
        'start_time' => '08:00',
        'end_time' => '16:00',
        'days_of_week' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        'working_hours' => 8,
    ];

    $response = $this->actingAs($user)->post(route('hr.shifts.store'), $data);

    $response->assertRedirect()
        ->assertSessionHas('success');
    $this->assertDatabaseHas('shifts', [
        'name' => 'Morning Shift',
        'code' => 'MS001',
        'start_time' => '08:00',
        'end_time' => '16:00',
        'working_hours' => 8,
    ]);
});

it('validates required fields on create', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('hr.shifts.store'), []);

    $response->assertSessionHasErrors(['name', 'code', 'start_time', 'end_time']);
});

it('updates a shift', function () {
    $user = User::factory()->create();
    $organization = \App\Models\Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $shift = Shift::factory()->create(['organization_id' => $organization->id]);

    $data = [
        'name' => 'Updated Shift',
        'code' => $shift->code,
        'start_time' => $shift->start_time,
        'end_time' => $shift->end_time,
        'days_of_week' => $shift->days_of_week,
    ];

    $response = $this->actingAs($user)->put(route('hr.shifts.update', $shift), $data);

    $response->assertRedirect()
        ->assertSessionHas('success');
    $this->assertDatabaseHas('shifts', ['name' => 'Updated Shift']);
});

it('deletes a shift', function () {
    $user = User::factory()->create();
    $organization = \App\Models\Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $shift = Shift::factory()->create(['organization_id' => $organization->id]);

    $response = $this->actingAs($user)->delete(route('hr.shifts.destroy', $shift));

    $response->assertRedirect()
        ->assertSessionHas('success');
    $this->assertDatabaseMissing('shifts', ['id' => $shift->id]);
});
