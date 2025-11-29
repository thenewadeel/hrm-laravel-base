<?php

use App\View\Components\Navigation\Breadcrumb;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BreadcrumbTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $component = new Breadcrumb;

        expect($component)->toBeInstanceOf(Breadcrumb::class);
    }

    #[Test]
    public function it_has_default_empty_pages_array()
    {
        $component = new Breadcrumb;

        expect($component->pages)->toBeArray()->toBeEmpty();
    }

    #[Test]
    public function it_accepts_pages_array_in_constructor()
    {
        $pages = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Dashboard', 'url' => '/dashboard'],
        ];

        $component = new Breadcrumb($pages);

        expect($component->pages)->toBe($pages);
    }

    #[Test]
    public function it_renders_breadcrumb_view()
    {
        $component = new Breadcrumb;

        $view = $component->render();

        expect($view->name())->toBe('components.navigation.breadcrumb');
    }

    #[Test]
    public function it_handles_empty_pages_gracefully()
    {
        $component = new Breadcrumb([]);

        expect($component->pages)->toBeArray()->toBeEmpty();
    }

    #[Test]
    public function it_handles_single_page_breadcrumb()
    {
        $pages = [['name' => 'Home', 'url' => '/']];
        $component = new Breadcrumb($pages);

        expect($component->pages)->toHaveCount(1);
        expect($component->pages[0])->toBe($pages[0]);
    }

    #[Test]
    public function it_handles_multiple_pages_breadcrumb()
    {
        $pages = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Users', 'url' => '/users'],
            ['name' => 'Profile', 'url' => '/users/profile'],
        ];
        $component = new Breadcrumb($pages);

        expect($component->pages)->toHaveCount(3);
        expect($component->pages)->toBe($pages);
    }

    #[Test]
    public function it_handles_pages_with_missing_url()
    {
        $pages = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Current Page'], // No URL for current page
        ];
        $component = new Breadcrumb($pages);

        expect($component->pages)->toHaveCount(2);
        expect($component->pages[1])->toBe($pages[1]);
    }

    #[Test]
    public function it_handles_pages_with_additional_attributes()
    {
        $pages = [
            ['name' => 'Home', 'url' => '/', 'icon' => 'home'],
            ['name' => 'Users', 'url' => '/users', 'active' => true],
        ];
        $component = new Breadcrumb($pages);

        expect($component->pages[0]['icon'])->toBe('home');
        expect($component->pages[1]['active'])->toBeTrue();
    }
}
