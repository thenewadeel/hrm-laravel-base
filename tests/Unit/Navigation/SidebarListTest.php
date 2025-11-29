<?php

use App\View\Components\Navigation\SidebarList;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SidebarListTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new SidebarList;

        expect($component)->toBeInstanceOf(SidebarList::class);
    }

    #[Test]
    public function it_renders_sidebar_list_view()
    {
        $component = new SidebarList;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.sidebar-list');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new SidebarList;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
