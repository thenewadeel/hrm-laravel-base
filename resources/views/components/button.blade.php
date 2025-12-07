@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $baseClasses =
        'inline-flex items-center justify-center rounded-md font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed focus-ring';

    $variants = [
        'primary' => 'bg-primary text-inverse hover:bg-primary-dark focus:ring-primary',
        'secondary' => 'surface border border-secondary text-primary hover:bg-secondary focus:ring-secondary',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-800',
        'outline' => 'border border-secondary text-primary hover:bg-secondary focus:ring-secondary',
        'ghost' => 'text-primary hover:bg-secondary focus:ring-secondary',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 dark:bg-green-700 dark:hover:bg-green-800',
        'warning' => 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500 dark:bg-yellow-700 dark:hover:bg-yellow-800',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        'xl' => 'px-8 py-4 text-lg',
    ];

    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<{{ $type }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $type }}>