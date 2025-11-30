<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiProgressBadge extends Component
{
    public int $progress;

    public function __construct(int $progress)
    {
        $this->progress = max(0, min(100, $progress));
    }

    public function getColor(): string
    {
        return match(true) {
            $this->progress >= 75 => 'green',
            $this->progress >= 50 => 'blue',
            $this->progress >= 25 => 'yellow',
            default => 'red'
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-progress-badge');
    }
}