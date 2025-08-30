<?php
// app/Http/Livewire/Organization/OrganizationList.php

namespace App\Livewire\Organization;

use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;

class OrganizationList extends Component
{
    use WithPagination;
    public static $route = '/organizations';
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10]
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatedPerPage()
    {
        $this->resetPage(); // Reset to first page when perPage changes
    }

    public function render()
    {
        $organizations = Organization::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.organization.organization-list', [
            'organizations' => $organizations
        ]);
    }
}
