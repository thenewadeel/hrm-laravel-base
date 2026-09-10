<?php

use App\View\Components\Navigation\MobileMenu;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MobileMenuTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new MobileMenu;

        expect($component)->toBeInstanceOf(MobileMenu::class);
    }

    #[Test]
    public function it_renders_mobile_menu_view()
    {
        $component = new MobileMenu;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.mobile-menu');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new MobileMenu;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(View::class);
    }
}
