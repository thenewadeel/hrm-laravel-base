<?php

use App\View\Components\Navigation\ThemeToggle;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ThemeToggleTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new ThemeToggle;

        expect($component)->toBeInstanceOf(ThemeToggle::class);
    }

    #[Test]
    public function it_has_default_dark_mode_false()
    {
        $component = new ThemeToggle;

        expect($component->darkMode)->toBeFalse();
    }

    #[Test]
    public function it_accepts_dark_mode_parameter()
    {
        $component = new ThemeToggle(true);

        expect($component->darkMode)->toBeTrue();
    }

    #[Test]
    public function it_accepts_dark_mode_false_explicitly()
    {
        $component = new ThemeToggle(false);

        expect($component->darkMode)->toBeFalse();
    }

    #[Test]
    public function it_renders_theme_toggle_view()
    {
        $component = new ThemeToggle;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.theme-toggle');
    }

    #[Test]
    public function it_renders_with_dark_mode_enabled()
    {
        $component = new ThemeToggle(true);

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.theme-toggle');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new ThemeToggle;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
