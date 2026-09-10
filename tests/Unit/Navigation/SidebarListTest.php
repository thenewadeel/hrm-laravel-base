<?php

use App\View\Components\Navigation\SidebarList;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

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
        expect($component->render())->toBeInstanceOf(View::class);
    }
}
