<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiRoleBadge extends Component
{
    public string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function getLabel(): string
    {
        return ucfirst($this->role);
    }

    public function getColor(): string
    {
        return match($this->role) {
            'admin', 'super_admin' => 'purple',
            'manager', 'supervisor' => 'blue',
            'user', 'employee' => 'green',
            'guest' => 'gray',
            default => 'gray'
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-role-badge');
    }
}