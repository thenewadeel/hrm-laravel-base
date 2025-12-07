<?php

use App\View\Components\Navigation\Main;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MainTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new Main;

        expect($component)->toBeInstanceOf(Main::class);
    }

    #[Test]
    public function it_renders_main_navigation_view()
    {
        $component = new Main;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.main');
    }

    #[Test]
    public function it_has_no_required_properties()
    {
        $component = new Main;

        // Component should work without any properties
        expect($component)->toBeInstanceOf(Main::class);
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new Main;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
