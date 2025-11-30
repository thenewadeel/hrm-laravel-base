<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiBadge extends Component
{
    public string $color;
    public string $size;
    public ?string $icon;
    public bool $dismissible;
    public bool $dot;
    public string $variant;

    public function __construct(
        string $color = 'gray',
        string $size = 'md',
        ?string $icon = null,
        bool $dismissible = false,
        bool $dot = false,
        string $variant = 'solid'
    ) {
        $this->color = $color;
        $this->size = $size;
        $this->icon = $icon;
        $this->dismissible = $dismissible;
        $this->dot = $dot;
        $this->variant = $variant;
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-badge');
    }
}