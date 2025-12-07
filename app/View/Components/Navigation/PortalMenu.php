<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class PortalMenu extends Component
{
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.portal-menu');
    }
}
