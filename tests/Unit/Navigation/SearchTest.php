<?php

use App\View\Components\Navigation\Search;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SearchTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new Search;

        expect($component)->toBeInstanceOf(Search::class);
    }

    #[Test]
    public function it_has_default_placeholder()
    {
        $component = new Search;

        expect($component->placeholder)->toBe('Search...');
    }

    #[Test]
    public function it_has_default_null_action()
    {
        $component = new Search;

        expect($component->action)->toBeNull();
    }

    #[Test]
    public function it_has_default_method_get()
    {
        $component = new Search;

        expect($component->method)->toBe('GET');
    }

    #[Test]
    public function it_accepts_custom_placeholder()
    {
        $component = new Search('Search users...');

        expect($component->placeholder)->toBe('Search users...');
    }

    #[Test]
    public function it_accepts_custom_action()
    {
        $component = new Search('Search...', '/search');

        expect($component->action)->toBe('/search');
    }

    #[Test]
    public function it_accepts_custom_method()
    {
        $component = new Search('Search...', null, 'POST');

        expect($component->method)->toBe('POST');
    }

    #[Test]
    public function it_accepts_all_parameters()
    {
        $component = new Search('Find items', '/items/search', 'GET');

        expect($component->placeholder)->toBe('Find items');
        expect($component->action)->toBe('/items/search');
        expect($component->method)->toBe('GET');
    }

    #[Test]
    public function it_handles_different_http_methods()
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

        foreach ($methods as $method) {
            $component = new Search('Search...', null, $method);
            expect($component->method)->toBe($method);
        }
    }

    #[Test]
    public function it_handles_empty_placeholder()
    {
        $component = new Search('');

        expect($component->placeholder)->toBe('');
    }

    #[Test]
    public function it_handles_empty_action()
    {
        $component = new Search('Search...', '');

        expect($component->action)->toBe('');
    }

    #[Test]
    public function it_renders_search_view()
    {
        $component = new Search;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.search');
    }

    #[Test]
    public function it_implements_component_contract()
    {
        $component = new Search;

        expect(method_exists($component, 'render'))->toBeTrue();
        expect($component->render())->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
    }
}
