<?php
// app/Http/Livewire/Organization/OrganizationForm.php

namespace App\Livewire\Organization;

use App\Models\Organization;
use Livewire\Component;
use Livewire\Attributes\On; // Add this import
use Livewire\Attributes\Validate; // For validation attributes

class OrganizationForm extends Component
{
    public ?Organization $organization = null;

    #[Validate('required|min:3|max:255|unique:organizations,name')]
    public $name = '';

    #[Validate('nullable|string|max:500')]
    public $description = '';

    #[Validate('boolean')]
    public $is_active = true;

    public $showModal = false;
    public $isEditing = false;

    // Use On attribute instead of $listeners array
    #[On('openOrganizationModal')]
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    #[On('editOrganization')]
    public function edit($id)
    {
        $this->organization = Organization::findOrFail($id);
        $this->name = $this->organization->name;
        $this->description = $this->organization->description;
        $this->is_active = (bool) $this->organization->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    // app/Http/Livewire/Organization/OrganizationForm.php

    public function save()
    {
        // Dynamic validation rules
        $rules = [
            'name' => 'required|min:3|max:255|unique:organizations,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];

        // For edit mode, ignore current organization in unique rule
        if ($this->isEditing && $this->organization) {
            $rules['name'] = 'required|min:3|max:255|unique:organizations,name,' . $this->organization->id;
        }

        $this->validate($rules);

        if ($this->isEditing) {
            $this->organization->update([
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            $message = 'Organization updated successfully!';
        } else {
            Organization::create([
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            $message = 'Organization created successfully!';
        }

        $this->closeModal();

        $this->dispatch('organizationSaved');
        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['organization', 'name', 'description', 'is_active', 'isEditing']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.organization.organization-form');
    }
}
