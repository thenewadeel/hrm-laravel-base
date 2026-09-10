<?php

namespace App\View\Components\Navigation;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ThemeToggle extends Component
{
    public bool $darkMode = false;

    public function __construct(bool $darkMode = false)
    {
        $this->darkMode = $darkMode;
    }

    public function render(): View
    {
        return view('components.navigation.theme-toggle');
    }
}
