<?php

use App\View\Components\Navigation\SidebarWidget;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SidebarWidgetTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new SidebarWidget;

        expect($component)->toBeInstanceOf(SidebarWidget::class);
    }

    #[Test]
    public function it_renders_sidebar_widget_view()
    {
        $component = new SidebarWidget;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.sidebar-widget');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new SidebarWidget;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(View::class);
    }
}
