<?php
// tests/Feature/Livewire/Organization/OrganizationFormTest.php

namespace Tests\Feature\Livewire\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SetupOrganization;
use PHPUnit\Framework\Attributes\Test;

class OrganizationFormTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
    }
    #[Test]
    public function it_shows_create_form()
    {
        [$organization, $user] = [$this->organization, $this->user];

        Livewire::actingAs($user)
            ->test('organization.organization-form')
            ->call('openModal')
            ->assertSet('isEditing', false)
            ->assertSet('showModal', true)
            ->assertSee('Create Organization');
    }

    #[Test]
    public function it_shows_edit_form()
    {
        [$organization, $user] = [$this->organization, $this->user];

        Livewire::actingAs($user)
            ->test('organization.organization-form')
            ->call('edit', $organization->id)
            ->assertSet('isEditing', true)
            ->assertSet('showModal', true)
            ->assertSet('name', $organization->name)
            ->assertSet('description', $organization->description)
            ->assertSet('is_active', $organization->is_active)
            ->assertSee('Edit Organization');
    }

    #[Test]
    public function it_creates_new_organization()
    {
        [$organization, $user] = [$this->organization, $this->user];


        Livewire::actingAs($user)
            ->test('organization.organization-form')
            ->set('name', 'Test Organization')
            ->set('description', 'Test description')
            ->set('is_active', true)
            ->call('save')
            ->assertDispatched('organizationSaved') // Changed from assertEmitted
            ->assertDispatched('notify'); // Changed from assertEmitted

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'description' => 'Test description',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_updates_existing_organization()
    {
        [$organization, $user] = [$this->organization, $this->user];

        Livewire::actingAs($user)
            ->test('organization.organization-form')
            ->call('edit', $organization->id)
            ->set('name', 'Updated Organization')
            ->set('description', 'Updated description')
            ->set('is_active', false)
            ->call('save')
            ->assertDispatched('organizationSaved')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Organization',
            'description' => 'Updated description',
            'is_active' => false,
        ]);
    }

    #[Test]
    public function it_validates_required_fields()
    {
        [$organization, $user] = [$this->organization, $this->user];

        Livewire::actingAs($user)
            ->test('organization.organization-form')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    #[Test]
    public function it_validates_unique_name()
    {
        [$organization, $user] = [$this->organization, $this->user];
        $existing = Organization::factory()->create(['name' => 'Existing Org']);

        Livewire::actingAs($user)
            ->test('organization.organization-form')
            ->set('name', 'Existing Org')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);
    }

    #[Test]
    public function it_closes_modal()
    {
        [$organization, $user] = [$this->organization, $this->user];

        Livewire::actingAs($user)
            ->test('organization.organization-form')
            ->call('openModal')
            ->assertSet('showModal', true)
            ->call('closeModal')
            ->assertSet('showModal', false);
    }
}
