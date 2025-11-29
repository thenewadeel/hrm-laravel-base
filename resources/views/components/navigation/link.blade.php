@props([
    'href' => '#',
    'active' => false,
    'icon' => null,
    'badge' => null,
    'target' => '_self',
])

@php
$baseClasses = 'flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ease-in-out';
$inactiveClasses = 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800';
$activeClasses = 'text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700';
$classes = $active 
    ? $baseClasses . ' ' . $activeClasses 
    : $baseClasses . ' ' . $inactiveClasses;
@endphp

<a href="{{ $href }}" 
   target="{{ $target }}" 
   {{ $attributes->merge(['class' => $classes]) }}>
    
    @if($icon)
        <span class="mr-3 flex items-center justify-center h-5 w-5 flex-shrink-0">
            {!! $icon !!}
        </span>
    @endif
    
    <span class="flex-1">
        {{ $slot }}
    </span>
    
    @if($badge)
        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
            {{ $badge }}
        </span>
    @endif
</a>