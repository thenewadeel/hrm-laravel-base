<?php

namespace App\View\Components\Navigation;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PortalDesktopMenu extends Component
{
    public function render(): View
    {
        return view('components.navigation.portal-desktop-menu');
    }
}
