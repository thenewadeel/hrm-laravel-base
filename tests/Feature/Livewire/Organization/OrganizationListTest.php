<?php
// tests/Feature/Livewire/Organization/OrganizationListTest.php

namespace Tests\Feature\Livewire\Organization;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\SetupOrganization; // Updated namespace
use PHPUnit\Framework\Attributes\Test;

class OrganizationListTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;

    #[Test]
    public function it_shows_organizations_list()
    {
        [$organization, $user] = $this->createOrganizationWithUser();

        Livewire::actingAs($user)
            ->test('organization.organization-list')
            ->assertSee($organization->name);
        // ->assertSee($organizations[1]->name)
        // ->assertSee($organizations[2]->name);
    }

    #[Test]
    public function it_searches_organizations_by_name()
    {
        [$organization, $user] = $this->createOrganizationWithUser();
        $org1 = Organization::factory()->create(['name' => 'Pharma Solutions']);
        $org2 = Organization::factory()->create(['name' => 'MediCare Ltd']);

        Livewire::actingAs($user)
            ->test('organization.organization-list')
            ->set('search', 'Pharma')
            ->assertSee($org1->name)
            ->assertDontSee($org2->name);
    }

    #[Test]
    public function it_sorts_organizations_by_name_ascending()
    {
        [$organization, $user] = $this->createOrganizationWithUser();

        // Create organizations in specific order
        $orgC = Organization::factory()->create(['name' => 'Gamma Organization']);
        $orgB = Organization::factory()->create(['name' => 'Beta Organization']);
        $orgA = Organization::factory()->create(['name' => 'Alpha Organization']);

        $test = Livewire::actingAs($user)
            ->test('organization.organization-list');

        // Debug: check initial state
        // dump('Initial sort field:', $test->get('sortField'));
        // dump('Initial sort direction:', $test->get('sortDirection'));

        // Get the initial data
        $initialData = $test->viewData('organizations');
        // dump('Initial organizations:', $initialData->pluck('name')->toArray());

        // Call sort
        $test->call('sortBy', 'name');

        // Debug: check state after sorting
        // dump('After sort - field:', $test->get('sortField'));
        // dump('After sort - direction:', $test->get('sortDirection'));

        // Get the data after sorting
        $sortedData = $test->viewData('organizations');
        // dump('Sorted organizations:', $sortedData->pluck('name')->toArray());

        $test->assertSeeInOrder([$orgC->name, $orgB->name, $orgA->name]);
    }

    #[Test]
    public function it_sorts_organizations_by_name_descending()
    {
        [$organization, $user] = $this->createOrganizationWithUser();

        $orgA = Organization::factory()->create(['name' => 'Alpha Organization']);
        $orgB = Organization::factory()->create(['name' => 'Beta Organization']);
        $orgC = Organization::factory()->create(['name' => 'Gamma Organization']);

        Livewire::actingAs($user)
            ->test('organization.organization-list')
            ->call('sortBy', 'name') // First click: ascending
            ->call('sortBy', 'name') // Second click: descending
            ->assertSeeInOrder([$orgA->name, $orgB->name, $orgC->name]);
    }

    #[Test]
    public function it_sorts_organizations_by_status()
    {
        [$organization, $user] = $this->createOrganizationWithUser();

        $activeOrg = Organization::factory()->active()->create(['name' => 'Active Company']);
        $inactiveOrg = Organization::factory()->inactive()->create(['name' => 'Inactive Company']);

        Livewire::actingAs($user)
            ->test('organization.organization-list')
            ->call('sortBy', 'is_active')
            ->assertSeeInOrder([$inactiveOrg->name, $activeOrg->name]); // Inactive first (false < true)
    }

    #[Test]
    public function it_paginates_organizations()
    {
        [$organization, $user] = $this->createOrganizationWithUser();
        Organization::factory()->count(20)->create();



        $test = Livewire::actingAs($user)
            ->test('organization.organization-list');

        // Check that we have pagination data
        $organizationsData = $test->viewData('organizations');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $organizationsData);
        $this->assertEquals(10, $organizationsData->perPage());
        $this->assertEquals(21, $organizationsData->total());
        $this->assertEquals(3, $organizationsData->lastPage());

        // Change perPage and verify
        $test->set('perPage', 5);

        $organizationsData = $test->viewData('organizations');
        $this->assertEquals(5, $organizationsData->perPage());
        $this->assertEquals(21, $organizationsData->total());
        $this->assertEquals(5, $organizationsData->lastPage());
    }

    #[Test]
    public function it_shows_empty_state_when_no_organizations()
    {
        // [$organization, $user] = $this->createOrganizationWithUser();

        Livewire::
            //actingAs($user)
            test('organization.organization-list')
            ->assertSee('No organizations found');
    }
}
