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
        'family-profile' => [
            'name' => 'Family Profile',
            'icon' => 'users',
            'description' => 'View complete family profiles',
        ],
        'card-management' => [
            'name' => 'Card Management',
            'icon' => 'id-card',
            'description' => 'Manage member cards and access',
        ],
        'user-registration' => [
            'name' => 'User Registration',
            'icon' => 'user-group',
            'description' => 'Individual and bulk registration',
        ],
        'fee-settings' => [
            'name' => 'Fee Settings',
            'icon' => 'cog',
            'description' => 'Configure fee rules and pricing',
        ],
        'collection-dashboard' => [
            'name' => 'Collection Dashboard',
            'icon' => 'chart-bar',
            'description' => 'Track payments and defaulters',
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
