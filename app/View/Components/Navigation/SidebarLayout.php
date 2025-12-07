<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class SidebarLayout extends Component
{
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.sidebar-layout');
    }
}
