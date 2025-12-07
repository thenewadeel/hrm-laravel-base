<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class DropdownLink extends Component
{
    public string $href;
    public ?string $icon = null;

    public function __construct(string $href = '#', ?string $icon = null)
    {
        $this->href = $href;
        $this->icon = $icon;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.dropdown-link');
    }
}
