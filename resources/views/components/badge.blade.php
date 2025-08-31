{{-- resources/views/components/badge.blade.php --}}
@props([
    'color' => 'gray',
    'size' => 'md',
])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-800 border-gray-200',
        'red' => 'bg-red-100 text-red-800 border-red-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'green' => 'bg-green-100 text-green-800 border-green-200',
        'blue' => 'bg-blue-100 text-blue-800 border-blue-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'purple' => 'bg-purple-100 text-purple-800 border-purple-200',
        'pink' => 'bg-pink-100 text-pink-800 border-pink-200',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs border',
        'md' => 'px-2.5 py-1 text-sm border',
        'lg' => 'px-3 py-1.5 text-base border',
    ];

    $baseClasses = 'inline-flex items-center font-medium rounded-full border';
    $colorClasses = $colors[$color] ?? $colors['gray'];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => "{$baseClasses} {$colorClasses} {$sizeClasses}"]) }}>
    {{ $slot }}
</span>
