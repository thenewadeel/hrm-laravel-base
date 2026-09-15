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
        'danger' => 'bg-error text-inverse hover:bg-error/90 focus:ring-error',
        'outline' => 'border border-secondary text-primary hover:bg-secondary focus:ring-secondary',
        'ghost' => 'text-primary hover:bg-secondary focus:ring-secondary',
        'success' => 'bg-success text-inverse hover:bg-success/90 focus:ring-success',
        'warning' => 'bg-warning text-inverse hover:bg-warning/90 focus:ring-warning',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        'xl' => 'px-8 py-4 text-lg',
    ];

    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

{{-- Always use the <button> element, control its 'type' attribute with the $type prop --}}
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
