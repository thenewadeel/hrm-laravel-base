<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class Search extends Component
{
    public string $placeholder = 'Search...';
    public ?string $action = null;
    public string $method = 'GET';

    public function __construct(
        string $placeholder = 'Search...',
        ?string $action = null,
        string $method = 'GET'
    ) {
        $this->placeholder = $placeholder;
        $this->action = $action;
        $this->method = $method;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.search');
    }
}
