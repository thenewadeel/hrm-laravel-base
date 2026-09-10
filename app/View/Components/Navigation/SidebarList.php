<?php

namespace App\View\Components\Navigation;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SidebarList extends Component
{
    public string $title;

    public array $items = [];

    public function __construct(string $title = '', array $items = [])
    {
        $this->title = $title;
        $this->items = $items;
    }

    public function render(): View
    {
        return view('components.navigation.sidebar-list');
    }
}
