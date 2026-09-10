<?php

use App\View\Components\Navigation\DesktopMenu;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DesktopMenuTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new DesktopMenu;

        expect($component)->toBeInstanceOf(DesktopMenu::class);
    }

    #[Test]
    public function it_renders_desktop_menu_view()
    {
        $component = new DesktopMenu;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.desktop-menu');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new DesktopMenu;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(View::class);
    }
}
