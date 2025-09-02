<?php

namespace App\Livewire;

use App\Models\OrganizationUnit;
use Livewire\Component;

class OrganizationTree extends Component
{
    public $roots;

    public $organizationId; // New public property for the filter

    public function mount($organizationId = null)
    {
        // $this->organizationId = $organizationId;
        $this->loadTree();
    }
    /**
     * This method is explicitly called when the filter changes.
     */
    public function filterByOrganization($id)
    {
        $this->organizationId = $id;
        $this->loadTree();
    }
    public function loadTree()
    {
        // Start with the base query for root units
        $query = OrganizationUnit::whereNull('parent_id')->with('children');

        // // Apply the filter if an organizationId is set
        // if ($this->organizationId) {
        //     $query->where('organization_id', $this->organizationId);
        // }
        if (is_array($this->organizationId) && !empty($this->organizationId)) {
            $query->whereIn('organization_id', $this->organizationId);
        } else if (!is_array($this->organizationId) && $this->organizationId) {
            $query->where('organization_id', $this->organizationId);
        }

        // Get the results
        $this->roots = $query->get();
    }

    // app/Livewire/OrganizationTree.php

    public function updateParent($unitId, $newParentId = null)
    {
        // Find the unit we are trying to move
        $unitToMove = OrganizationUnit::find($unitId);

        // If the unit doesn't exist, do nothing
        if (!$unitToMove) {
            return;
        }

        // 1. Prevent a unit from being dropped onto itself.
        if ((int)$unitId === (int)$newParentId) {
            return;
        }

        // 2. Prevent a parent from being dropped onto one of its descendants.
        if ($newParentId) {
            $potentialParent = OrganizationUnit::find($newParentId);

            // Traverse up the hierarchy from the potential new parent.
            // If we ever find the unit being moved, it means the drop target is a descendant.
            $currentNode = $potentialParent;
            while ($currentNode) {
                if ((int)$currentNode->id === (int)$unitToMove->id) {
                    return; // Abort the operation.
                }
                $currentNode = $currentNode->parent;
            }
        }

        // If all validation passes, update the parent ID and save.
        $unitToMove->parent_id = $newParentId;
        $unitToMove->save();

        // Reload the entire tree to reflect the changes.
        $this->loadTree();
    }


    public function render()
    {
        return view('livewire.organization-tree');
    }
}
