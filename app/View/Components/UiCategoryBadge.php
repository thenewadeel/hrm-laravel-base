<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UiCategoryBadge extends Component
{
    public string $category;

    public function __construct(string $category)
    {
        $this->category = $category;
    }

    public function getLabel(): string
    {
        return ucfirst($this->category);
    }

    public function getColor(): string
    {
        return match($this->category) {
            'sales', 'revenue' => 'green',
            'marketing', 'promotion' => 'purple',
            'support', 'service' => 'blue',
            'development', 'tech' => 'indigo',
            'hr', 'human' => 'pink',
            'finance', 'accounting' => 'yellow',
            default => 'gray'
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.ui-category-badge');
    }
}