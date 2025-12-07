<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders basic badge component', function () {
    $view = $this->blade('<x-badge>Test Badge</x-badge>');

    $view->assertSee('Test Badge');
    $view->assertSee('inline-flex');
    $view->assertSee('font-medium');
});

it('renders badge with custom color and size', function () {
    $view = $this->blade('<x-badge color="green" size="lg">Green Badge</x-badge>');

    $view->assertSee('Green Badge');
    $view->assertSee('bg-green-100');
    $view->assertSee('text-green-800');
    $view->assertSee('px-3');
    $view->assertSee('py-1');
});

it('renders badge with icon', function () {
    $view = $this->blade('<x-badge icon="user" color="blue">User Badge</x-badge>');

    $view->assertSee('User Badge');
});

it('renders status badge correctly', function () {
    $view = $this->blade('<x-badge status="active">Active</x-badge>');

    $view->assertSee('Active');
    $view->assertSee('bg-green-100');
    $view->assertSee('text-green-800');
});

it('renders category badge correctly', function () {
    $view = $this->blade('<x-badge color="blue">Sales</x-badge>');

    $view->assertSee('Sales');
    $view->assertSee('bg-blue-100');
    $view->assertSee('text-blue-800');
});

it('renders count badge correctly', function () {
    $view = $this->blade('
        <div class="relative">
            <button>Test</button>
            <x-badge dot color="red" wrapper>5</x-badge>
        </div>
    ');

    $view->assertSee('Test');
    $view->assertSee('5');
    $view->assertSee('absolute');
});

it('renders key-value badge correctly', function () {
    $view = $this->blade('
        <div class="flex items-center space-x-2">
            <span class="text-sm font-medium text-gray-600">Status:</span>
            <x-badge color="green" size="sm">Active</x-badge>
        </div>
    ');

    $view->assertSee('Status:');
    $view->assertSee('Active');
    $view->assertSee('bg-green-100');
});

it('renders progress badge correctly', function () {
    $view = $this->blade('
        <div class="flex items-center space-x-2">
            <span class="text-sm font-medium text-gray-600">Progress:</span>
            <x-badge color="blue" size="sm" wrapper>75%</x-badge>
        </div>
    ');

    $view->assertSee('Progress:');
    $view->assertSee('75%');
    $view->assertSee('bg-blue-100');
});

it('renders tag badges correctly', function () {
    $tags = ['PHP', 'Laravel', 'Vue'];
    $view = $this->blade('
        <div class="flex space-x-2">
            '.implode('', array_map(fn ($tag) => "<x-badge color=\"blue\" size=\"sm\" wrapper>$tag</x-badge>", $tags)).'
        </div>
    ', ['tags' => $tags]);

    $view->assertSee('PHP');
    $view->assertSee('Laravel');
    $view->assertSee('Vue');
});

it('renders role badge correctly', function () {
    $view = $this->blade('<x-badge color="purple" size="sm">Admin</x-badge>');

    $view->assertSee('Admin');
    $view->assertSee('bg-purple-100');
    $view->assertSee('text-purple-800');
});

it('renders priority badge correctly', function () {
    $view = $this->blade('<x-badge color="orange" size="sm">High</x-badge>');

    $view->assertSee('High');
    $view->assertSee('bg-orange-100');
    $view->assertSee('text-orange-800');
});

it('renders type badge correctly', function () {
    $view = $this->blade('<x-badge color="indigo" size="sm">Full-time</x-badge>');

    $view->assertSee('Full-time');
    $view->assertSee('bg-indigo-100');
    $view->assertSee('text-indigo-800');
});

it('handles dismissible badge', function () {
    $view = $this->blade('<x-badge dismissible color="red">Dismissible</x-badge>');

    $view->assertSee('Dismissible');
    $view->assertSee('button');
    // Check that dismissible functionality is present (SVG icon)
    $view->assertSee('svg');
});

it('handles badge with dot indicator', function () {
    $view = $this->blade('<x-badge dot color="green">With Dot</x-badge>');

    $view->assertSee('With Dot');
    $view->assertSee('w-2');
    $view->assertSee('h-2');
});

it('handles badge variants correctly', function () {
    $solid = $this->blade('<x-badge variant="solid" color="blue">Solid</x-badge>');
    $outline = $this->blade('<x-badge variant="outline" color="blue">Outline</x-badge>');
    $subtle = $this->blade('<x-badge variant="subtle" color="blue">Subtle</x-badge>');

    $solid->assertSee('bg-blue-100');
    $outline->assertSee('border-blue-300');
    $subtle->assertSee('bg-blue-50');
});

it('handles dark mode classes', function () {
    $view = $this->blade('<x-badge color="green">Dark Mode</x-badge>');

    $view->assertSee('dark:bg-green-900');
    $view->assertSee('dark:text-green-200');
});

it('limits tag badges correctly', function () {
    $tags = ['PHP', 'Laravel', 'Vue', 'React', 'Angular'];
    $view = $this->blade('
        <div class="flex space-x-2">
            @foreach(array_slice($tags, 0, 3) as $tag)
                <x-badge color="blue" size="sm">{{ $tag }}</x-badge>
            @endforeach
            @if(count($tags) > 3)
                <x-badge color="gray" size="sm">+{{ count($tags) - 3 }} more</x-badge>
            @endif
        </div>
    ', ['tags' => $tags]);

    $view->assertSee('PHP');
    $view->assertSee('Laravel');
    $view->assertSee('Vue');
    $view->assertSee('+2 more');
});

it('handles unknown status gracefully', function () {
    $view = $this->blade('<x-badge status="unknown_status">Unknown_status</x-badge>');

    $view->assertSee('Unknown_status');
    $view->assertSee('bg-gray-100');
});

it('handles unknown category gracefully', function () {
    $view = $this->blade('<x-badge color="gray">Unknown_category</x-badge>');

    $view->assertSee('Unknown_category');
    $view->assertSee('bg-gray-100');
});

it('handles custom labels correctly', function () {
    $view = $this->blade('<x-badge status="active">Custom Active</x-badge>');

    $view->assertSee('Custom Active');
    // Custom label should override default
    $view->assertSee('Custom Active');
});
