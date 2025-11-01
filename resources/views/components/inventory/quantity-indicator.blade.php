@props(['quantity', 'reorderLevel'])

@php
    if ($quantity <= 0) {
        $color = 'text-red-600 bg-red-100';
        $text = 'Out of Stock';
    } elseif ($quantity <= $reorderLevel) {
        $color = 'text-yellow-600 bg-yellow-100';
        $text = 'Low Stock';
    } else {
        $color = 'text-green-600 bg-green-100';
        $text = 'In Stock';
    }
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
    {{ $text }}
</span>
