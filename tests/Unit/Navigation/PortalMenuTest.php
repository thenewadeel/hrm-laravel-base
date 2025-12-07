<?php

use App\View\Components\Navigation\PortalMenu;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PortalMenuTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new PortalMenu;

        expect($component)->toBeInstanceOf(PortalMenu::class);
    }

    #[Test]
    public function it_renders_portal_menu_view()
    {
        $component = new PortalMenu;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.portal-menu');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new PortalMenu;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
