@props([
    'href' => '#',
    'icon' => null,
])

@php
$classes = 'block w-full text-left px-4 py-2 text-sm text-primary hover:bg-surface hover:text-primary transition-colors duration-150 ease-in-out';
@endphp

<a href="{{ $href }}" 
   {{ $attributes->merge(['class' => $classes]) }}>
    
    @if($icon)
        <span class="mr-3 flex items-center justify-center h-4 w-4 flex-shrink-0">
            {!! $icon !!}
        </span>
    @endif
    
    {{ $slot }}
</a>