<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiKeyValueBadge extends Component
{
    public string $label;
    public string $value;
    public string $color;

    public function __construct(string $label, string $value, string $color = 'gray')
    {
        $this->label = $label;
        $this->value = $value;
        $this->color = $color;
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-key-value-badge');
    }
}