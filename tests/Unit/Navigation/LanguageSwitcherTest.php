<?php

use App\View\Components\Navigation\LanguageSwitcher;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LanguageSwitcherTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new LanguageSwitcher;

        expect($component)->toBeInstanceOf(LanguageSwitcher::class);
    }

    #[Test]
    public function it_renders_language_switcher_view()
    {
        $component = new LanguageSwitcher;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.language-switcher');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new LanguageSwitcher;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
