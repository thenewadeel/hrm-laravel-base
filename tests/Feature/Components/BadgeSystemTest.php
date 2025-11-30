<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders basic badge component', function () {
    $view = $this->blade('<x-ui-badge>Test Badge</x-ui-badge>');

    $view->assertSee('Test Badge');
    $view->assertSee('inline-flex');
    $view->assertSee('font-medium');
});

it('renders badge with custom color and size', function () {
    $view = $this->blade('<x-ui-badge color="green" size="lg">Green Badge</x-ui-badge>');

    $view->assertSee('Green Badge');
    $view->assertSee('bg-green-100');
    $view->assertSee('text-green-800');
    $view->assertSee('px-3');
    $view->assertSee('py-1');
});

it('renders badge with icon', function () {
    $view = $this->blade('<x-ui-badge icon="user" color="blue">User Badge</x-ui-badge>');

    $view->assertSee('User Badge');
});

it('renders status badge correctly', function () {
    $view = $this->blade('<x-ui-status-badge status="active" />');

    $view->assertSee('Active');
    $view->assertSee('bg-green-100');
    $view->assertSee('text-green-800');
});

it('renders category badge correctly', function () {
    $view = $this->blade('<x-ui-category-badge category="sales" />');

    $view->assertSee('Sales');
    $view->assertSee('bg-green-100');
    $view->assertSee('text-green-800');
});

it('renders count badge correctly', function () {
    $view = $this->blade('
        <div class="relative">
            <button>Test</button>
            <x-ui-count-badge count="5" />
        </div>
    ');

    $view->assertSee('Test');
    $view->assertSee('5');
    $view->assertSee('absolute');
});

it('renders key-value badge correctly', function () {
    $view = $this->blade('<x-ui-key-value-badge label="Status" value="Active" color="green" />');

    $view->assertSee('Status:');
    $view->assertSee('Active');
    $view->assertSee('bg-green-100');
});

it('renders progress badge correctly', function () {
    $view = $this->blade('<x-ui-progress-badge progress="75" />');

    $view->assertSee('75%');
    $view->assertSee('Progress');
    $view->assertSee('bg-blue-200');
});

it('renders tag badges correctly', function () {
    $tags = ['PHP', 'Laravel', 'Vue'];
    $view = $this->blade('<x-ui-tag-badges :tags="$tags" />', ['tags' => $tags]);

    $view->assertSee('PHP');
    $view->assertSee('Laravel');
    $view->assertSee('Vue');
});

it('renders role badge correctly', function () {
    $view = $this->blade('<x-ui-role-badge role="admin" />');

    $view->assertSee('Admin');
    $view->assertSee('bg-purple-100');
    $view->assertSee('text-purple-800');
});

it('renders priority badge correctly', function () {
    $view = $this->blade('<x-ui-priority-badge priority="high" />');

    $view->assertSee('High');
    $view->assertSee('bg-orange-100');
    $view->assertSee('text-orange-800');
});

it('renders type badge correctly', function () {
    $view = $this->blade('<x-ui-type-badge type="pdf" />');

    $view->assertSee('PDF');
    $view->assertSee('bg-red-100');
    $view->assertSee('text-red-800');
});

it('handles dismissible badge', function () {
    $view = $this->blade('<x-ui-badge dismissible color="red">Dismissible</x-ui-badge>');

    $view->assertSee('Dismissible');
    $view->assertSee('button');
    // Check that dismissible functionality is present (SVG icon)
    $view->assertSee('svg');
});

it('handles badge with dot indicator', function () {
    $view = $this->blade('<x-ui-badge dot color="green">With Dot</x-ui-badge>');

    $view->assertSee('With Dot');
    $view->assertSee('w-2');
    $view->assertSee('h-2');
});

it('handles badge variants correctly', function () {
    $solid = $this->blade('<x-ui-badge variant="solid" color="blue">Solid</x-ui-badge>');
    $outline = $this->blade('<x-ui-badge variant="outline" color="blue">Outline</x-ui-badge>');
    $subtle = $this->blade('<x-ui-badge variant="subtle" color="blue">Subtle</x-ui-badge>');

    $solid->assertSee('bg-blue-100');
    $outline->assertSee('border-blue-300');
    $subtle->assertSee('bg-blue-50');
});

it('handles dark mode classes', function () {
    $view = $this->blade('<x-ui-badge color="green">Dark Mode</x-ui-badge>');

    $view->assertSee('dark:bg-green-900');
    $view->assertSee('dark:text-green-200');
});

it('limits tag badges correctly', function () {
    $tags = ['PHP', 'Laravel', 'Vue', 'React', 'Angular'];
    $view = $this->blade('<x-ui-tag-badges :tags="$tags" limit="3" />', ['tags' => $tags]);

    $view->assertSee('PHP');
    $view->assertSee('Laravel');
    $view->assertSee('Vue');
    $view->assertSee('+2 more');
});

it('handles unknown status gracefully', function () {
    $view = $this->blade('<x-ui-status-badge status="unknown_status" />');

    $view->assertSee('Unknown_status');
    $view->assertSee('bg-gray-100');
});

it('handles unknown category gracefully', function () {
    $view = $this->blade('<x-ui-category-badge category="unknown_category" />');

    $view->assertSee('Unknown_category');
    $view->assertSee('bg-gray-100');
});

it('handles custom labels correctly', function () {
    $view = $this->blade('<x-ui-status-badge status="active" custom-label="Custom Active" />');

    $view->assertSee('Custom Active');
    // Custom label should override default
    $view->assertSee('Custom Active');
});
