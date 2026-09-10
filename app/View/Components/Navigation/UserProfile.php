<?php

namespace App\View\Components\Navigation;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserProfile extends Component
{
    public ?User $user = null;

    public bool $showRole = true;

    public bool $showStatus = true;

    public bool $showAvatar = true;

    public string $size = 'md'; // sm, md, lg

    public function __construct(
        ?User $user = null,
        bool $showRole = true,
        bool $showStatus = true,
        bool $showAvatar = true,
        string $size = 'md'
    ) {
        $this->user = $user;
        $this->showRole = $showRole;
        $this->showStatus = $showStatus;
        $this->showAvatar = $showAvatar;
        $this->size = $size;
    }

    public function render(): View
    {
        return view('components.navigation.user-profile');
    }
}
