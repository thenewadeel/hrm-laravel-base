<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class Breadcrumb extends Component
{
    public array $pages = [];

    public function __construct(array $pages = [])
    {
        $this->pages = $pages;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.breadcrumb');
    }
}
