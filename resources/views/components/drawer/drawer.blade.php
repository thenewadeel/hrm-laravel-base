@props([
    'position' => 'left',
    'title' => '',
    'width' => '320px',
    'height' => '320px',
    'class' => ''
])

@php
    $drawerId = 'drawer-' . $position;
@endphp

 {{-- Alpine.js takes complete control here --}}
<div x-show="drawers.{{ $position }}"
     x-transition:enter="transition ease-in-out duration-300"
     x-transition:enter-start="{{ $position === 'left' ? 'transform -translate-x-full opacity-0' : ($position === 'right' ? 'transform translate-x-full opacity-0' : ($position === 'top' ? 'transform -translate-y-full opacity-0' : 'transform translate-y-full opacity-0')) }}"
     x-transition:enter-end="transform translate-0 opacity-100"
     x-transition:leave="transition ease-out duration-200"
     x-transition:leave-start="transform translate-0 opacity-100"
     x-transition:leave-end="{{ $position === 'left' ? 'transform -translate-x-full opacity-0' : ($position === 'right' ? 'transform translate-x-full opacity-0' : ($position === 'top' ? 'transform -translate-y-full opacity-0' : 'transform translate-y-full opacity-0')) }}"
     class="drawer drawer-{{ $position }} {{ $class }} {{ $position === 'left' || $position === 'right' ? 'w-80' : '' }} transition-transform overflow-y-auto overscroll-contain z-50"
     style="{{ $position === 'left' || $position === 'right' ? 'width: ' . $width : 'height: ' . $height }}"
     role="region"
     :aria-label="'{{ $title }} drawer'"
     :aria-hidden="!drawers.{{ $position }}"
     id="{{ $drawerId }}"
     x-cloak>
    
    {{-- Drawer Header --}}
    @if($title)
    <div class="drawer-header">
        <h3 class="text-lg font-semibold">{{ $title }}</h3>
        <button @click="closeDrawer('{{ $position }}')"
                type="button"
                class="p-1 rounded-md hover:bg-secondary text-secondary hover:text-primary transition-colors"
                :aria-label="'Close {{ $title }} drawer'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    @endif
    
    {{-- Drawer Content --}}
    <div class="drawer-content" style="height: calc(100% - {{ $title ? '60px' : '0px' }});">
        {{ $slot }}
    </div>
</div>