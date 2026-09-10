<?php

namespace App\View\Components\Navigation;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumb extends Component
{
    public array $pages = [];

    public function __construct(array $pages = [])
    {
        $this->pages = $pages;
    }

    public function render(): View
    {
        return view('components.navigation.breadcrumb');
    }
}
