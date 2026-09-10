<?php

use App\View\Components\Navigation\PortalDesktopMenu;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PortalDesktopMenuTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new PortalDesktopMenu;

        expect($component)->toBeInstanceOf(PortalDesktopMenu::class);
    }

    #[Test]
    public function it_renders_portal_desktop_menu_view()
    {
        $component = new PortalDesktopMenu;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.portal-desktop-menu');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new PortalDesktopMenu;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(View::class);
    }
}
