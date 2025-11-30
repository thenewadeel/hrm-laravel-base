@props([
    'role',
    'size' => 'md',
    'showIcon' => true,
    'customLabel' => null,
])

@php
    $roleConfig = [
        // System Roles
        'super_admin' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'shield-check',
            'label' => 'Super Admin',
        ],
        'admin' => [
            'color' => 'purple',
            'variant' => 'solid',
            'icon' => 'shield-check',
            'label' => 'Admin',
        ],
        'manager' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'user-group',
            'label' => 'Manager',
        ],
        'supervisor' => [
            'color' => 'indigo',
            'variant' => 'solid',
            'icon' => 'eye',
            'label' => 'Supervisor',
        ],
        
        // HR Roles
        'hr_manager' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'users',
            'label' => 'HR Manager',
        ],
        'hr_specialist' => [
            'color' => 'emerald',
            'variant' => 'solid',
            'icon' => 'user-plus',
            'label' => 'HR Specialist',
        ],
        'recruiter' => [
            'color' => 'teal',
            'variant' => 'solid',
            'icon' => 'magnifying-glass',
            'label' => 'Recruiter',
        ],
        
        // Finance Roles
        'accountant' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'calculator',
            'label' => 'Accountant',
        ],
        'finance_manager' => [
            'color' => 'cyan',
            'variant' => 'solid',
            'icon' => 'banknotes',
            'label' => 'Finance Manager',
        ],
        'auditor' => [
            'color' => 'orange',
            'variant' => 'solid',
            'icon' => 'clipboard-document-check',
            'label' => 'Auditor',
        ],
        
        // Employee Roles
        'employee' => [
            'color' => 'gray',
            'variant' => 'subtle',
            'icon' => 'user',
            'label' => 'Employee',
        ],
        'senior_employee' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'star',
            'label' => 'Senior Employee',
        ],
        'lead' => [
            'color' => 'purple',
            'variant' => 'solid',
            'icon' => 'crown',
            'label' => 'Team Lead',
        ],
        
        // Access Levels
        'full_access' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'key',
            'label' => 'Full Access',
        ],
        'limited_access' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'key',
            'label' => 'Limited Access',
        ],
        'read_only' => [
            'color' => 'gray',
            'variant' => 'outline',
            'icon' => 'eye',
            'label' => 'Read Only',
        ],
        'no_access' => [
            'color' => 'red',
            'variant' => 'subtle',
            'icon' => 'lock-closed',
            'label' => 'No Access',
        ],
        
        // Department Specific
        'sales_rep' => [
            'color' => 'emerald',
            'variant' => 'solid',
            'icon' => 'phone',
            'label' => 'Sales Rep',
        ],
        'support_agent' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'headphones',
            'label' => 'Support Agent',
        ],
        'developer' => [
            'color' => 'indigo',
            'variant' => 'solid',
            'icon' => 'code-bracket',
            'label' => 'Developer',
        ],
        'designer' => [
            'color' => 'pink',
            'variant' => 'solid',
            'icon' => 'palette',
            'label' => 'Designer',
        ],
    ];

    $config = $roleConfig[$role] ?? [
        'color' => 'gray',
        'variant' => 'subtle',
        'icon' => 'user',
        'label' => ucfirst(str_replace('_', ' ', $role)),
    ];
@endphp

<x-new-badge 
    :color="$config['color']"
    :variant="$config['variant']"
    :size="$size"
    :icon="$showIcon ? $config['icon'] : null"
    {{ $attributes }}
>
    {{ $customLabel ?? $config['label'] }}
</x-new-badge>