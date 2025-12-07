<?php

// app/Livewire/UserPlacement.php

namespace App\Livewire;

use App\Livewire\Traits\ManagesOrganizationFilter;
use App\Models\Organization;
use App\Models\User;
use Livewire\Component;

class UserPlacement extends Component
{
    use ManagesOrganizationFilter;

    public $organizations; // All organizations for the filter

    public $organizationId;

    public $unassignedUsers;

    public $search = '';

    public function mount($organizationId = null)
    {
        if ($organizationId) {
            $this->organizationId = $organizationId;
        }
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
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->get();
    }

    // app/Livewire/UserPlacement.php

    // app/Livewire/UserPlacement.php

    // app/Livewire/UserPlacement.php

    public function assignUserToUnit($userId, $unitId)
    {
        $user = \App\Models\User::find($userId);

        if (! $user) {
            return;
        }

        // Check if user is already attached to this organization
        $existingPivot = $user->organizations()
            ->where('organization_id', $this->organizationId)
            ->first();

        if ($existingPivot) {
            // Update existing pivot record directly using DB
            $affected = \Illuminate\Support\Facades\DB::table('organization_user')
                ->where('user_id', $userId)
                ->where('organization_id', $this->organizationId)
                ->update(['organization_unit_id' => $unitId]);

            // If no rows were affected, it might be because the user isn't attached to this org
            if ($affected === 0) {
                // Try to attach the user to this organization first
                $user->organizations()->attach($this->organizationId, [
                    'organization_unit_id' => $unitId,
                    'roles' => json_encode([]),
                ]);
            }
        } else {
            // Attach user to organization with unit
            $user->organizations()->attach($this->organizationId, [
                'organization_unit_id' => $unitId,
                'roles' => json_encode([]),
            ]);
        }

        // Re-fetch data to update the UI
        $this->loadUnassignedUsers();
    }

    public function render()
    {
        $organization = Organization::find($this->organizationId);
        $treeRoots = $organization ? $organization->units()->whereNull('parent_id')->get() : collect();

        return view('livewire.user-placement', [
            'treeRoots' => $treeRoots,
            'organizationName' => $organization ? $organization->name : 'No Organization Selected',
        ]);
    }
}
