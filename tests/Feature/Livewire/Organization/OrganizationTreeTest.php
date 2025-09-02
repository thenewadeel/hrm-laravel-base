<?php

namespace Tests\Feature\Livewire;

use App\Livewire\OrganizationTree;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationTreeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_display_the_organization_tree()
    {
        // 1. Arrange: Create a sample organization tree structure.
        $root = OrganizationUnit::factory()->create(['name' => 'CEO Office']);
        $child1 = OrganizationUnit::factory()->create(['name' => 'Marketing', 'parent_id' => $root->id]);
        $grandchild = OrganizationUnit::factory()->create(['name' => 'Social Media', 'parent_id' => $child1->id]);
        $child2 = OrganizationUnit::factory()->create(['name' => 'Sales', 'parent_id' => $root->id]);

        // 2. Act: Render the Livewire component.
        Livewire::test('organization-tree')
            ->assertStatus(200)
            ->assertSee($root->name)
            ->assertSee($child1->name)
            ->assertSee($grandchild->name)
            ->assertSee($child2->name);
    }

    /** @test */
    public function an_organization_unit_can_be_dragged_and_dropped_to_another_parent()
    {
        // 1. Arrange: Create a sample organization tree.
        $ceoOffice = OrganizationUnit::factory()->create(['name' => 'CEO Office']);
        $marketing = OrganizationUnit::factory()->create(['name' => 'Marketing', 'parent_id' => $ceoOffice->id]);
        $sales = OrganizationUnit::factory()->create(['name' => 'Sales', 'parent_id' => $ceoOffice->id]);
        $hr = OrganizationUnit::factory()->create(['name' => 'HR', 'parent_id' => $ceoOffice->id]);

        // 2. Act: Simulate the drag-and-drop event.
        // We'll pass the ID of the dragged item and the ID of the new parent.
        Livewire::test('organization-tree')
            ->call('updateParent', $marketing->id, $hr->id)
            ->assertStatus(200);

        // 3. Assert: Check if the database has been updated correctly.
        $this->assertEquals($hr->id, $marketing->fresh()->parent_id);
        $this->assertNotEquals($ceoOffice->id, $marketing->fresh()->parent_id);
    }


    /** @test */
    public function an_organizational_unit_cannot_be_dropped_onto_itself()
    {
        // Arrange
        $unit = OrganizationUnit::factory()->create(['name' => 'Department A']);

        // Act
        Livewire::test(OrganizationTree::class)
            ->call('updateParent', $unit->id, $unit->id);

        // Assert
        $this->assertEquals(null, $unit->fresh()->parent_id);
    }

    /** @test */
    public function an_organizational_unit_can_be_dropped_as_a_root_node()
    {
        // Arrange
        $root = OrganizationUnit::factory()->create(['name' => 'Root']);
        $child = OrganizationUnit::factory()->create(['name' => 'Child', 'parent_id' => $root->id]);

        // Act
        Livewire::test(OrganizationTree::class)
            ->call('updateParent', $child->id, null);

        // Assert
        $this->assertEquals(null, $child->fresh()->parent_id);
    }

    /** @test */
    public function a_parent_cannot_be_dropped_onto_one_of_its_descendants()
    {
        // Arrange
        $grandparent = OrganizationUnit::factory()->create(['name' => 'Grandparent']);
        $parent = OrganizationUnit::factory()->create(['name' => 'Parent', 'parent_id' => $grandparent->id]);
        $child = OrganizationUnit::factory()->create(['name' => 'Child', 'parent_id' => $parent->id]);
        $grandchild = OrganizationUnit::factory()->create(['name' => 'Grandchild', 'parent_id' => $child->id]);

        // Assert: The 'Grandparent' should not be able to be dropped on 'Grandchild'
        Livewire::test(OrganizationTree::class)
            ->call('updateParent', $grandparent->id, $grandchild->id);

        $this->assertEquals(null, $grandparent->fresh()->parent_id);
    }

    /** @test */
    public function a_sibling_can_be_dropped_under_another_sibling()
    {
        // Arrange
        $root = OrganizationUnit::factory()->create(['name' => 'Root']);
        $siblingA = OrganizationUnit::factory()->create(['name' => 'Sibling A', 'parent_id' => $root->id]);
        $siblingB = OrganizationUnit::factory()->create(['name' => 'Sibling B', 'parent_id' => $root->id]);

        // Act
        Livewire::test(OrganizationTree::class)
            ->call('updateParent', $siblingB->id, $siblingA->id);

        // Assert
        $this->assertEquals($siblingA->id, $siblingB->fresh()->parent_id);
    }

    /** @test */
    public function an_empty_tree_renders_correctly()
    {
        // Arrange: No units in the database
        $this->assertEquals(0, OrganizationUnit::count());

        // Act & Assert
        Livewire::test(OrganizationTree::class)
            ->assertSee('No organizational units found.');
    }

    /** @test */
    public function it_can_handle_a_large_number_of_units_without_crashing()
    {
        // Arrange
        OrganizationUnit::factory()->count(100)->create();

        // Act & Assert: Just rendering the component should work without errors
        Livewire::test(OrganizationTree::class)
            ->assertOk();
    }

    /** @test */
    public function a_dropped_unit_that_does_not_exist_does_not_cause_an_error()
    {
        // Arrange
        $nonExistentId = 9999;
        $targetId = OrganizationUnit::factory()->create()->id;

        // Act
        Livewire::test(OrganizationTree::class)
            ->call('updateParent', $nonExistentId, $targetId);

        // Assert: The database state should be unchanged
        $this->assertEquals(1, OrganizationUnit::count());
    }
}
