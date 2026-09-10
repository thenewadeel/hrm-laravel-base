<?php

use App\View\Components\Navigation\PortalQuickLinks;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PortalQuickLinksTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new PortalQuickLinks;

        expect($component)->toBeInstanceOf(PortalQuickLinks::class);
    }

    #[Test]
    public function it_renders_portal_quick_links_view()
    {
        $component = new PortalQuickLinks;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.portal-quick-links');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new PortalQuickLinks;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(View::class);
    }
}
