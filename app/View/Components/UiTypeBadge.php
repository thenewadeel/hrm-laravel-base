<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiTypeBadge extends Component
{
    public string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getLabel(): string
    {
        return strtoupper($this->type);
    }

    public function getColor(): string
    {
        return match($this->type) {
            'pdf' => 'red',
            'doc', 'docx' => 'blue',
            'xls', 'xlsx' => 'green',
            'ppt', 'pptx' => 'orange',
            'txt', 'md' => 'gray',
            'zip', 'rar' => 'purple',
            default => 'gray'
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-type-badge');
    }
}