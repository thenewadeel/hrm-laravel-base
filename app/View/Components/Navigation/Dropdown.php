<?php

namespace App\View\Components\Navigation;

use Illuminate\View\Component;

class Dropdown extends Component
{
    public string $align = 'left';
    public string $width = '48';
    public string $contentClasses = 'py-1 bg-white dark:bg-gray-700';
    public string $dropdownClasses = '';

    public function __construct(
        string $align = 'left',
        string $width = '48',
        string $contentClasses = 'py-1 bg-white dark:bg-gray-700',
        string $dropdownClasses = ''
    ) {
        $this->align = $align;
        $this->width = $width;
        $this->contentClasses = $contentClasses;
        $this->dropdownClasses = $dropdownClasses;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('components.navigation.dropdown');
    }
}