<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class NotificationBell extends Component
{
    public int $count = 0;
    public bool $showCount = true;

    public function __construct(
        int $count = 0,
        bool $showCount = true
    ) {
        $this->count = $count;
        $this->showCount = $showCount;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.notification-bell');
    }
}
