<?php

namespace App\View\Components\Navigation;

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

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.section');
    }
}
