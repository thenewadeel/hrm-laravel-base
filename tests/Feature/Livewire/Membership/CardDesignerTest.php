<?php

use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

test('card designer renders without person', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\CardDesigner::class)
        ->assertStatus(200);
});

test('card designer renders with member', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    $member = Member::factory()->create(['organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\CardDesigner::class, ['member' => $member])
        ->assertStatus(200);
});

test('batch mode can be toggled', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\CardDesigner::class)
        ->assertSet('batchMode', false)
        ->call('toggleBatchMode')
        ->assertSet('batchMode', true);
});

test('card template can be changed', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    $member = Member::factory()->create(['organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\CardDesigner::class, ['member' => $member])
        ->set('cardTemplate', 'premium')
        ->assertSet('cardTemplate', 'premium');
});

test('design settings can be updated', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    $member = Member::factory()->create(['organization_id' => $organization->id]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\CardDesigner::class, ['member' => $member])
        ->set('designSettings.primary_color', '#ff0000')
        ->assertSet('designSettings.primary_color', '#ff0000');
});

test('member card can be created', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $member = Member::factory()->create(['organization_id' => $organization->id]);

    $component = Livewire::actingAs($user)
        ->test(\App\Livewire\Membership\CardDesigner::class)
        ->set('member', $member)
        ->set('cardType', 'premium')
        ->set('cardTemplate', 'premium');

    // Check that component has been set with member
    $component->assertSet('member.id', $member->id);

    // Call saveCard and check for success dispatch
    $component->call('saveCard')
        ->assertDispatched('card-saved')
        ->assertDispatched('show-notification', message: 'Card saved successfully', type: 'success');

    $this->assertDatabaseHas('member_cards', [
        'member_id' => $member->id,
        'card_type' => 'premium',
        'template' => 'premium',
        'status' => 'active',
    ]);
});
