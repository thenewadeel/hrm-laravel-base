<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class MobileLink extends Component
{
    public string $title;
    public ?string $icon = null;
    public bool $active = false;

    public function __construct(
        string $title = '',
        ?string $icon = null,
        bool $active = false
    ) {
        $this->title = $title;
        $this->icon = $icon;
        $this->active = $active;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.mobile-link');
    }
}
