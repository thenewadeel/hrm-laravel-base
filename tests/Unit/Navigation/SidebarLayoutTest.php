<?php

use App\View\Components\Navigation\SidebarLayout;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SidebarLayoutTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new SidebarLayout;

        expect($component)->toBeInstanceOf(SidebarLayout::class);
    }

    #[Test]
    public function it_renders_sidebar_layout_view()
    {
        $component = new SidebarLayout;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.sidebar-layout');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new SidebarLayout;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(View::class);
    }
}
