<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiStatusBadge extends Component
{
    public string $status;
    public ?string $customLabel;

    public function __construct(string $status, ?string $customLabel = null)
    {
        $this->status = $status;
        $this->customLabel = $customLabel;
    }

    public function getLabel(): string
    {
        return $this->customLabel ?? ucfirst($this->status);
    }

    public function getColor(): string
    {
        return match($this->status) {
            'active', 'completed', 'success' => 'green',
            'inactive', 'failed', 'error' => 'red',
            'pending', 'warning' => 'yellow',
            'processing', 'info' => 'blue',
            default => 'gray'
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-status-badge');
    }
}