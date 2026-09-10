<?php

use App\View\Components\Navigation\NotificationBell;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new NotificationBell;

        expect($component)->toBeInstanceOf(NotificationBell::class);
    }

    #[Test]
    public function it_renders_notification_bell_view()
    {
        $component = new NotificationBell;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.notification-bell');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new NotificationBell;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(View::class);
    }
}
