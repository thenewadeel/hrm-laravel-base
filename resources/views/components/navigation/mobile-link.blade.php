@props([
    'title' => '',
    'icon' => null,
    'active' => false,
    'badge' => null,
])

@php
$baseClasses = 'flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ease-in-out w-full text-left';
$inactiveClasses = 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800';
$activeClasses = 'text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700';
$classes = $active 
    ? $baseClasses . ' ' . $activeClasses 
    : $baseClasses . ' ' . $inactiveClasses;
@endphp

<button type="button" 
        {{ $attributes->merge(['class' => $classes]) }}
        @click="$wire.dispatch('close-mobile-menu')">
    
    @if($icon)
        <span class="mr-3 h-5 w-5 flex-shrink-0">
            {!! $icon !!}
        </span>
    @endif
    
    <span class="flex-1">
        {{ $title }}
    </span>
    
    @if($badge)
        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
            {{ $badge }}
        </span>
    @endif
</button>