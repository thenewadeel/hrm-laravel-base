<?php

namespace App\View\Components\Navigation;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Section extends Component
{
    public string $title;

    public ?string $icon = null;

    public function __construct(string $title = '', ?string $icon = null)
    {
        $this->title = $title;
        $this->icon = $icon;
    }

    public function render(): View
    {
        return view('components.navigation.section');
    }
}
