<?php

namespace App\View\Components\Navigation;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PortalQuickLinks extends Component
{
    public function render(): View
    {
        return view('components.navigation.portal-quick-links');
    }
}
