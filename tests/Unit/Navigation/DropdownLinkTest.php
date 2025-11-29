<?php

use App\View\Components\Navigation\DropdownLink;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DropdownLinkTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new DropdownLink;

        expect($component)->toBeInstanceOf(DropdownLink::class);
    }

    #[Test]
    public function it_renders_dropdown_link_view()
    {
        $component = new DropdownLink;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.dropdown-link');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new DropdownLink;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
