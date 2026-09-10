<?php

use App\View\Components\Navigation\Section;
use Illuminate\Contracts\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SectionTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new Section;

        expect($component)->toBeInstanceOf(Section::class);
    }

    #[Test]
    public function it_renders_section_view()
    {
        $component = new Section;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.section');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new Section;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(View::class);
    }
}
