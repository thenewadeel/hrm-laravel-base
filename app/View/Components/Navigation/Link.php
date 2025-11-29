<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class Link extends Component
{
    public string $href;
    public bool $active = false;
    public ?string $icon = null;
    public ?string $badge = null;
    public string $target = '_self';

    public function __construct(
        string $href = '#',
        bool $active = false,
        ?string $icon = null,
        ?string $badge = null,
        string $target = '_self'
    ) {
        $this->href = $href;
        $this->active = $active;
        $this->icon = $icon;
        $this->badge = $badge;
        $this->target = $target;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.link');
    }
}
