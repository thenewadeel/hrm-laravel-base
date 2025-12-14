@props([
    'target' => '',
    'class' => '',
    'label' => 'Toggle drawer'
])

@php
    if (empty($target)) {
        throw new InvalidArgumentException('Target drawer ID is required');
    }
    
    // Extract position from target (e.g., 'drawer-left' -> 'left')
    $position = str_replace('drawer-', '', $target);
@endphp

<button @click="toggleDrawer('{{ $position }}')"
        type="button"
        role="button"
        class="drawer-toggle {{ $class }} inline-flex items-center justify-center p-2 rounded-md hover:bg-secondary text-secondary hover:text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        :aria-expanded="drawers['{{ $position }}']"
        aria-controls="{{ $target }}"
        aria-label="{{ $label }}">
    {{ $slot }}
</button>