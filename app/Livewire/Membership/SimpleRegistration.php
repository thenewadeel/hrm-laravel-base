<?php

namespace App\Livewire\Membership;

use Livewire\Component;

class SimpleRegistration extends Component
{
    public array $formData = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'membership_type' => 'basic',
        'payment_method' => 'card',
    ];

    public array $membershipTypes = [
        'basic' => ['name' => 'Basic Monthly', 'price' => 50, 'features' => ['Gym Access', 'Basic Equipment']],
        'premium' => ['name' => 'Premium Monthly', 'price' => 75, 'features' => ['Gym Access', 'All Equipment', 'Group Classes']],
        'family' => ['name' => 'Family Monthly', 'price' => 120, 'features' => ['Family Access', 'All Equipment', 'Group Classes', 'Kids Area']],
    ];

    public bool $showSuccess = false;

    protected array $rules = [
        'formData.first_name' => 'required|string|min:2',
        'formData.last_name' => 'required|string|min:2',
        'formData.email' => 'required|email',
        'formData.phone' => 'required|string|min:10',
        'formData.membership_type' => 'required|in:basic,premium,family',
        'formData.payment_method' => 'required|in:card,cash,bank',
    ];

    public function register(): void
    {
        $this->validate();

        // Simulate registration process
        usleep(1000000); // 1 second delay to simulate processing

        $this->showSuccess = true;

        // Reset form after 3 seconds
        $this->dispatch('reset-form');
    }

    public function resetForm(): void
    {
        $this->formData = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'membership_type' => 'basic',
            'payment_method' => 'card',
        ];
        $this->showSuccess = false;
    }

    public function render()
    {
        return view('livewire.membership.simple-registration');
    }
}
