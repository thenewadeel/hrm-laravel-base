<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class SidebarWidget extends Component
{
    public string $title;
    public array $items = [];

    public function __construct(string $title = '', array $items = [])
    {
        $this->title = $title;
        $this->items = $items;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.sidebar-widget');
    }
}
