<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Livewire\Traits\ManagesOrganizationFilter;
use Livewire\Component;

class MemberManager extends Component
{
    use ManagesOrganizationFilter;
    public $organizations;
    public $organizationId;
    public $search = '';

    public function mount()
    {
        $this->mountManagesOrganizationFilter();
    }

    public function updated($property)
    {
        // This will re-render the component when the search or organizationId changes
    }


    public function filterByOrganization($id)
    {
        $this->organizationId = $id;
    }

    public function render()
    {
        $members = collect();

        if ($this->organizationId) {
            $organization = Organization::find($this->organizationId);

            if ($organization) {
                $members = $organization->users()
                    ->when($this->search, function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->get();
            }
        }

        return view('livewire.member-manager', [
            'members' => $members,
            'organizations' => $this->organizations // Pass organizations to the view
        ]);
    }
}
