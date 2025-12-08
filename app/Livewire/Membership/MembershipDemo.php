<?php

namespace App\Livewire\Membership;

use Livewire\Component;

class MembershipDemo extends Component
{
    public string $activeTab = 'scanner';

    public array $tabs = [
        'scanner' => [
            'name' => 'Member Access',
            'icon' => 'credit-card',
            'description' => 'Scan member cards and manage access',
        ],
        'fees' => [
            'name' => 'Fee Management',
            'icon' => 'currency-dollar',
            'description' => 'Overview of fees and payments',
        ],
        'subscriptions' => [
            'name' => 'Subscriptions',
            'icon' => 'arrow-path',
            'description' => 'Manage member subscriptions',
        ],
        'registration' => [
            'name' => 'Registration',
            'icon' => 'user-plus',
            'description' => 'Register new members',
        ],
    ];

    public function switchTab(string $tab): void
    {
        if (isset($this->tabs[$tab])) {
            $this->activeTab = $tab;
        }
    }

    public function render()
    {
        return view('livewire.membership.membership-demo');
    }
}
