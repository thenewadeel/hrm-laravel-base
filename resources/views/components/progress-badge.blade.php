@props([
    'progress' => 0,
    'max' => 100,
    'color' => 'blue',
    'size' => 'md',
    'showPercentage' => true,
    'animated' => false,
    'variant' => 'solid',
])

@php
    $percentage = min(100, max(0, ($progress / $max) * 100));
    
    $sizeClasses = [
        'xs' => 'h-1 text-xs',
        'sm' => 'h-1.5 text-xs',
        'md' => 'h-2 text-sm',
        'lg' => 'h-3 text-base',
        'xl' => 'h-4 text-lg',
    ];
    
    $colorClasses = [
        'gray' => 'bg-gray-200 dark:bg-gray-700',
        'red' => 'bg-red-200 dark:bg-red-900/30',
        'yellow' => 'bg-yellow-200 dark:bg-yellow-900/30',
        'green' => 'bg-green-200 dark:bg-green-900/30',
        'blue' => 'bg-blue-200 dark:bg-blue-900/30',
        'indigo' => 'bg-indigo-200 dark:bg-indigo-900/30',
        'purple' => 'bg-purple-200 dark:bg-purple-900/30',
        'pink' => 'bg-pink-200 dark:bg-pink-900/30',
    ];
    
    $progressColorClasses = [
        'gray' => 'bg-gray-500',
        'red' => 'bg-red-500',
        'yellow' => 'bg-yellow-500',
        'green' => 'bg-green-500',
        'blue' => 'bg-blue-500',
        'indigo' => 'bg-indigo-500',
        'purple' => 'bg-purple-500',
        'pink' => 'bg-pink-500',
    ];
    
    $currentSizeClasses = $sizeClasses[$size] ?? $sizeClasses['md'];
    $currentColorClasses = $colorClasses[$color] ?? $colorClasses['blue'];
    $currentProgressColorClasses = $progressColorClasses[$color] ?? $progressColorClasses['blue'];
@endphp

<div class="w-full">
    @if($showPercentage)
        <div class="flex justify-between items-center mb-1">
            <x-new-badge 
                :color="$color"
                :variant="$variant"
                size="sm"
            >
                Progress
            </x-new-badge>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ round($percentage) }}%
            </span>
        </div>
    @endif
    
    <div class="w-full {{ $currentColorClasses }} rounded-full overflow-hidden {{ $currentSizeClasses }}">
        <div 
            class="h-full {{ $currentProgressColorClasses }} rounded-full transition-all duration-500 ease-out"
            style="width: {{ $percentage }}%"
            @if($animated)
                class="animate-pulse"
            @endif
        ></div>
    </div>
    
    @if(isset($attributes['label']))
        <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
            {{ $attributes['label'] }}
        </div>
    @endif
</div>