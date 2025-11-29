<?php

use App\View\Components\Navigation\Dropdown;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DropdownTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new Dropdown;

        expect($component)->toBeInstanceOf(Dropdown::class);
    }

    #[Test]
    public function it_has_default_alignment_left()
    {
        $component = new Dropdown;

        expect($component->align)->toBe('left');
    }

    #[Test]
    public function it_has_default_width_48()
    {
        $component = new Dropdown;

        expect($component->width)->toBe('48');
    }

    #[Test]
    public function it_accepts_custom_alignment()
    {
        $component = new Dropdown('right');

        expect($component->align)->toBe('right');
    }

    #[Test]
    public function it_accepts_custom_width()
    {
        $component = new Dropdown('left', '64');

        expect($component->width)->toBe('64');
    }

    #[Test]
    public function it_accepts_both_custom_alignment_and_width()
    {
        $component = new Dropdown('center', '56');

        expect($component->align)->toBe('center');
        expect($component->width)->toBe('56');
    }

    #[Test]
    public function it_renders_dropdown_view()
    {
        $component = new Dropdown;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.dropdown');
    }

    #[Test]
    public function it_handles_valid_alignments()
    {
        $alignments = ['left', 'right', 'center'];

        foreach ($alignments as $alignment) {
            $component = new Dropdown($alignment);
            expect($component->align)->toBe($alignment);
        }
    }

    #[Test]
    public function it_handles_various_widths()
    {
        $widths = ['32', '48', '56', '64', '72'];

        foreach ($widths as $width) {
            $component = new Dropdown('left', $width);
            expect($component->width)->toBe($width);
        }
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new Dropdown;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
