<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiPriorityBadge extends Component
{
    public string $priority;

    public function __construct(string $priority)
    {
        $this->priority = $priority;
    }

    public function getLabel(): string
    {
        return ucfirst($this->priority);
    }

    public function getColor(): string
    {
        return match($this->priority) {
            'critical', 'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray'
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-priority-badge');
    }
}