<?php

use App\View\Components\Navigation\MobileLink;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MobileLinkTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new MobileLink;

        expect($component)->toBeInstanceOf(MobileLink::class);
    }

    #[Test]
    public function it_renders_mobile_link_view()
    {
        $component = new MobileLink;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.mobile-link');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new MobileLink;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
