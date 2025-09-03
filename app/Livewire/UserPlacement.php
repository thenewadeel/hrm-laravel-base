<?php

// app/Livewire/UserPlacement.php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\User;
use Livewire\Component;
use App\Livewire\Traits\ManagesOrganizationFilter;

class UserPlacement extends Component
{
    use ManagesOrganizationFilter;
    public $organizations; // All organizations for the filter
    public $organizationId;
    public $unassignedUsers;
    public $search = '';

    public function mount()
    {
        $this->mountManagesOrganizationFilter();
        $this->loadUnassignedUsers();
    }

    public function updated($property)
    {
        if ($property === 'search' || $property === 'organizationId') {
            $this->loadUnassignedUsers();
        }
    }

    public function filterByOrganization($id)
    {
        $this->organizationId = $id;
    }
    public function updatedSearch($value)
    {
        $this->loadUnassignedUsers();
    }


    // app/Livewire/UserPlacement.php

    // app/Livewire/UserPlacement.php

    // app/Livewire/UserPlacement.php

    public function loadUnassignedUsers()
    {
        $this->unassignedUsers = \App\Models\User::where(function ($query) {
            $query->whereDoesntHave('organizations')
                ->orWhereHas('organizations', function ($query) {
                    $query->where('organization_id', $this->organizationId)
                        ->whereNull('organization_unit_id');
                });
        })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->get();
    }

    // app/Livewire/UserPlacement.php

    // app/Livewire/UserPlacement.php

    // app/Livewire/UserPlacement.php

    public function assignUserToUnit($userId, $unitId)
    {
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return;
        }

        $data = ['organization_unit_id' => $unitId];

        // Use syncWithoutDetaching to ensure the user is attached to the organization
        // and correctly update the pivot data without detaching from other units.
        $user->organizations()->syncWithoutDetaching([
            $this->organizationId => $data
        ]);

        // Re-fetch data to update the UI
        $this->loadUnassignedUsers();
    }

    public function render()
    {
        $organization = Organization::find($this->organizationId);
        $treeRoots = $organization ? $organization->units()->whereNull('parent_id')->get() : collect();

        return view('livewire.user-placement', [
            'treeRoots' => $treeRoots,
            'organizationName' => $organization ? $organization->name : 'No Organization Selected'
        ]);
    }
}
