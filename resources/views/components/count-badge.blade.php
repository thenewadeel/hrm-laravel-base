@props([
    'count',
    'max' => 99,
    'color' => 'red',
    'size' => 'sm',
    'showZero' => false,
    'pulse' => false,
    'position' => 'top-right',
])

@php
    $displayCount = $count > $max ? "{$max}+" : $count;
    
    $sizeClasses = [
        'xs' => 'min-w-[1rem] h-4 text-xs',
        'sm' => 'min-w-[1.25rem] h-5 text-xs',
        'md' => 'min-w-[1.5rem] h-6 text-sm',
        'lg' => 'min-w-[1.75rem] h-7 text-sm',
    ];
    
    $positionClasses = [
        'top-right' => '-top-2 -right-2',
        'top-left' => '-top-2 -left-2',
        'bottom-right' => '-bottom-2 -right-2',
        'bottom-left' => '-bottom-2 -left-2',
    ];
    
    $colorClasses = [
        'gray' => 'bg-gray-500 text-white',
        'red' => 'bg-red-500 text-white',
        'yellow' => 'bg-yellow-500 text-white',
        'green' => 'bg-green-500 text-white',
        'blue' => 'bg-blue-500 text-white',
        'indigo' => 'bg-indigo-500 text-white',
        'purple' => 'bg-purple-500 text-white',
        'pink' => 'bg-pink-500 text-white',
    ];
    
    $shouldShow = ($showZero && $count === 0) || $count > 0;
    $currentSizeClasses = $sizeClasses[$size] ?? $sizeClasses['sm'];
    $currentPositionClasses = $positionClasses[$position] ?? $positionClasses['top-right'];
    $currentColorClasses = $colorClasses[$color] ?? $colorClasses['red'];
@endphp

@if($shouldShow)
    <span 
        {{ $attributes->merge([
            'class' => "absolute inline-flex items-center justify-center rounded-full font-bold {$currentSizeClasses} {$currentPositionClasses} {$currentColorClasses} ring-2 ring-white dark:ring-gray-900 z-10"
        ]) }}
        @if($pulse)
            class="animate-pulse"
        @endif
    >
        {{ $displayCount }}
    </span>
@endif