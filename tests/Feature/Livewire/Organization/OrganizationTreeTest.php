<?php

namespace Tests\Feature\Livewire\Organization;

use App\Livewire\OrganizationTree;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupOrganization;

class OrganizationTreeTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
    }
    #[Test]
    public function it_can_display_the_organization_tree()
    {
        // 1. Arrange: Create a sample organization tree structure.
        $root = OrganizationUnit::factory()->create([
            'name' => 'CEO Office',
            'organization_id' => $this->organization->id
        ]);
        $child1 = OrganizationUnit::factory()->create([
            'name' => 'Marketing', 
            'parent_id' => $root->id,
            'organization_id' => $this->organization->id
        ]);
        $grandchild = OrganizationUnit::factory()->create([
            'name' => 'Social Media', 
            'parent_id' => $child1->id,
            'organization_id' => $this->organization->id
        ]);
        $child2 = OrganizationUnit::factory()->create([
            'name' => 'Sales', 
            'parent_id' => $root->id,
            'organization_id' => $this->organization->id
        ]);

        // 2. Act: Render the Livewire component with organization filter.
        Livewire::test('organization-tree', ['organizationId' => $this->organization->id])
            ->assertStatus(200)
            ->assertSee($root->name)
            ->assertSee($child1->name)
            ->assertSee($grandchild->name)
            ->assertSee($child2->name);
    }

    #[Test]
    public function an_organization_unit_can_be_dragged_and_dropped_to_another_parent()
    {
        // 1. Arrange: Create a sample organization tree.
        $ceoOffice = OrganizationUnit::factory()->create([
            'name' => 'CEO Office',
            'organization_id' => $this->organization->id
        ]);
        $marketing = OrganizationUnit::factory()->create([
            'name' => 'Marketing', 
            'parent_id' => $ceoOffice->id,
            'organization_id' => $this->organization->id
        ]);
        $sales = OrganizationUnit::factory()->create([
            'name' => 'Sales', 
            'parent_id' => $ceoOffice->id,
            'organization_id' => $this->organization->id
        ]);
        $hr = OrganizationUnit::factory()->create([
            'name' => 'HR', 
            'parent_id' => $ceoOffice->id,
            'organization_id' => $this->organization->id
        ]);

        // 2. Act: Simulate the drag-and-drop event.
        // We'll pass the ID of the dragged item and the ID of the new parent.
        Livewire::test('organization-tree', ['organizationId' => $this->organization->id])
            ->call('updateParent', $marketing->id, $hr->id)
            ->assertStatus(200);

        // 3. Assert: Check if the database has been updated correctly.
        $this->assertEquals($hr->id, $marketing->fresh()->parent_id);
        $this->assertNotEquals($ceoOffice->id, $marketing->fresh()->parent_id);
    }


    #[Test]
    public function an_organizational_unit_cannot_be_dropped_onto_itself()
    {
        // Arrange
        $unit = OrganizationUnit::factory()->create([
            'name' => 'Department A',
            'organization_id' => $this->organization->id
        ]);

        // Act
        Livewire::test(OrganizationTree::class, ['organizationId' => $this->organization->id])
            ->call('updateParent', $unit->id, $unit->id);

        // Assert
        $this->assertEquals(null, $unit->fresh()->parent_id);
    }

    #[Test]
    public function an_organizational_unit_can_be_dropped_as_a_root_node()
    {
        // Arrange
        $root = OrganizationUnit::factory()->create([
            'name' => 'Root',
            'organization_id' => $this->organization->id
        ]);
        $child = OrganizationUnit::factory()->create([
            'name' => 'Child', 
            'parent_id' => $root->id,
            'organization_id' => $this->organization->id
        ]);

        // Act
        Livewire::test(OrganizationTree::class, ['organizationId' => $this->organization->id])
            ->call('updateParent', $child->id, null);

        // Assert
        $this->assertEquals(null, $child->fresh()->parent_id);
    }

    #[Test]
    public function a_parent_cannot_be_dropped_onto_one_of_its_descendants()
    {
        // Arrange
        $grandparent = OrganizationUnit::factory()->create([
            'name' => 'Grandparent',
            'organization_id' => $this->organization->id
        ]);
        $parent = OrganizationUnit::factory()->create([
            'name' => 'Parent', 
            'parent_id' => $grandparent->id,
            'organization_id' => $this->organization->id
        ]);
        $child = OrganizationUnit::factory()->create([
            'name' => 'Child', 
            'parent_id' => $parent->id,
            'organization_id' => $this->organization->id
        ]);
        $grandchild = OrganizationUnit::factory()->create([
            'name' => 'Grandchild', 
            'parent_id' => $child->id,
            'organization_id' => $this->organization->id
        ]);

        // Assert: The 'Grandparent' should not be able to be dropped on 'Grandchild'
        Livewire::test(OrganizationTree::class, ['organizationId' => $this->organization->id])
            ->call('updateParent', $grandparent->id, $grandchild->id);

        $this->assertEquals(null, $grandparent->fresh()->parent_id);
    }

    #[Test]
    public function a_sibling_can_be_dropped_under_another_sibling()
    {
        // Arrange
        $root = OrganizationUnit::factory()->create([
            'name' => 'Root',
            'organization_id' => $this->organization->id
        ]);
        $siblingA = OrganizationUnit::factory()->create([
            'name' => 'Sibling A', 
            'parent_id' => $root->id,
            'organization_id' => $this->organization->id
        ]);
        $siblingB = OrganizationUnit::factory()->create([
            'name' => 'Sibling B', 
            'parent_id' => $root->id,
            'organization_id' => $this->organization->id
        ]);

        // Act
        Livewire::test(OrganizationTree::class, ['organizationId' => $this->organization->id])
            ->call('updateParent', $siblingB->id, $siblingA->id);

        // Assert
        $this->assertEquals($siblingA->id, $siblingB->fresh()->parent_id);
    }

    #[Test]
    public function an_empty_tree_renders_correctly()
    {
        // Arrange: Clean up any existing units
        OrganizationUnit::query()->forceDelete();
        $this->assertEquals(0, OrganizationUnit::count());

        // Act & Assert
        Livewire::test(OrganizationTree::class)
            ->assertSee('No organizational units found.');
    }

    #[Test]
    public function it_can_handle_a_large_number_of_units_without_crashing()
    {
        // Arrange
        OrganizationUnit::factory()->count(100)->create([
            'organization_id' => $this->organization->id
        ]);

        // Act & Assert: Just rendering the component should work without errors
        Livewire::test(OrganizationTree::class, ['organizationId' => $this->organization->id])
            ->assertOk();
    }

    #[Test]
    public function a_dropped_unit_that_does_not_exist_does_not_cause_an_error()
    {
        // Arrange
        $nonExistentId = 9999;
        $targetId = OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id
        ])->id;

        // Act
        Livewire::test(OrganizationTree::class, ['organizationId' => $this->organization->id])
            ->call('updateParent', $nonExistentId, $targetId);

        // Assert: The database state should be unchanged (excluding setup data)
        $initialCount = OrganizationUnit::count();
        $this->assertGreaterThanOrEqual(1, $initialCount);
    }
}
