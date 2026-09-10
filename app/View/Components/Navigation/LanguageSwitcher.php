<?php

namespace App\View\Components\Navigation;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LanguageSwitcher extends Component
{
    public string $currentLocale = 'en';

    public array $availableLocales = [];

    public function __construct(
        string $currentLocale = 'en',
        array $availableLocales = []
    ) {
        $this->currentLocale = $currentLocale;
        $this->availableLocales = $availableLocales;
    }

    public function render(): View
    {
        return view('components.navigation.language-switcher');
    }
}
