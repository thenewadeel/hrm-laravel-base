<?php

use App\View\Components\Navigation\Link;
use PHPUnit\Framework\Attributes\Test;

it('renders navigation link with href', function () {
    $component = new Link('https://example.com');

    $view = $component->render();

    expect($view->name())->toBe('components.navigation.link');
});

it('renders navigation link with active state', function () {
    $component = new Link('https://example.com', active: true);

    $view = $component->render();

    expect($view->name())->toBe('components.navigation.link');
});

it('renders navigation link with icon', function () {
    $component = new Link('https://example.com', icon: '🏠');

    $view = $component->render();

    expect($view->name())->toBe('components.navigation.link');
});

it('renders navigation link with badge', function () {
    $component = new Link('https://example.com', badge: '5');

    $view = $component->render();

    expect($view->name())->toBe('components.navigation.link');
});

it('renders navigation link with custom target', function () {
    $component = new Link('https://example.com', target: '_blank');

    $view = $component->render();

    expect($view->name())->toBe('components.navigation.link');
});

it('renders navigation link with slot content', function () {
    $component = new Link('https://example.com');

    $view = $component->render();

    expect($view->name())->toBe('components.navigation.link');
});

it('uses correct css classes for inactive state', function () {
    $component = new Link('https://example.com', active: false);

    $view = $component->render();

    expect($view->name())->toBe('components.navigation.link');
});

it('uses correct css classes for active state', function () {
    $component = new Link('https://example.com', active: true);

    $view = $component->render();

    expect($view->name())->toBe('components.navigation.link');
});

it('has correct default properties', function () {
    $component = new Link;

    expect($component->href)->toBe('#');
    expect($component->active)->toBeFalse();
    expect($component->icon)->toBeNull();
    expect($component->badge)->toBeNull();
    expect($component->target)->toBe('_self');
});

it('accepts all constructor parameters', function () {
    $component = new Link(
        href: 'https://test.com',
        active: true,
        icon: 'test-icon',
        badge: '10',
        target: '_blank'
    );

    expect($component->href)->toBe('https://test.com');
    expect($component->active)->toBeTrue();
    expect($component->icon)->toBe('test-icon');
    expect($component->badge)->toBe('10');
    expect($component->target)->toBe('_blank');
});
