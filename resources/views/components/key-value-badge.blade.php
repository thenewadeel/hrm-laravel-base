@props([
    'label',
    'value',
    'color' => 'gray',
    'size' => 'md',
    'variant' => 'solid',
    'showColon' => true,
])

@php
    $sizeClasses = [
        'xs' => 'text-xs',
        'sm' => 'text-xs',
        'md' => 'text-sm',
        'lg' => 'text-base',
        'xl' => 'text-lg',
    ];
    
    $currentSizeClasses = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div class="inline-flex items-center gap-2">
    <x-new-badge 
        :color="$color"
        :variant="$variant"
        :size="$size"
        {{ $attributes->except('value') }}
    >
        {{ $label }}{{ $showColon ? ':' : '' }}
    </x-new-badge>
    
    @if($value !== null)
        <span class="font-medium {{ $currentSizeClasses }} text-gray-900 dark:text-gray-100">
            {{ $value }}
        </span>
    @endif
</div>