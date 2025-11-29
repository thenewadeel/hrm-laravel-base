<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;
use App\Models\User;

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

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.user-profile');
    }
}
