<?php

namespace App\Livewire\Traits;

use App\Models\Organization;

trait ManagesOrganizationFilter
{
    public $organizations;
    public $organizationId;

    public function mountManagesOrganizationFilter()
    {
        $this->organizations = Organization::all();
        if ($this->organizations->isNotEmpty()) {
            $this->organizationId = $this->organizations->first()->id;
        }
    }

    public function filterByOrganization($id)
    {
        $this->organizationId = $id;
    }
}
