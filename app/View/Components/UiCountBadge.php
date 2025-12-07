<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiCountBadge extends Component
{
    public int $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-count-badge');
    }
}