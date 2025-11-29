<?php

use App\View\Components\Navigation\QuickActions;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class QuickActionsTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new QuickActions;

        expect($component)->toBeInstanceOf(QuickActions::class);
    }

    #[Test]
    public function it_renders_quick_actions_view()
    {
        $component = new QuickActions;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.quick-actions');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new QuickActions;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
