<?php

namespace App\Livewire;

use App\Models\Organization;
use Livewire\Component;

class MemberManager extends Component
{
    public $organizations;
    public $organizationId;
    public $search = '';

    public function mount()
    {
        // Fetch all organizations on mount
        $this->organizations = Organization::all();
    }

    public function filterByOrganization($id)
    {
        $this->organizationId = $id;
    }

    public function render()
    {
        $members = collect();

        if ($this->organizationId) {
            $organization = $this->organizations->find($this->organizationId);

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
            'members' => $members
        ]);
    }
}
