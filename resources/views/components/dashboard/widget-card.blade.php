@props([
    'key' => null,
    'widget' => null,
    'activity' => [],
])

@php
    $span = match ($widget['type'] ?? null) {
        'area', 'feed' => 'lg:col-span-2',
        default => 'lg:col-span-1',
    };
    $isChart = in_array($widget['type'] ?? null, ['area', 'bars', 'donut', 'spark', 'gauge'], true);
@endphp

<div wire:key="widget-{{ $key }}"
     class="dashboard-widget {{ $span }}"
     data-widget-key="{{ $key }}"
     draggable="true">
    {{-- The x-card outer div carries the surface/rounding; the inner element in
         the original partial becomes the card's own wrapper so drag + resize
         hooks keep working unchanged. --}}
    <div class="dashboard-widget-inner surface h-full flex flex-col overflow-hidden rounded-2xl border border-secondary"
         data-widget="{{ $key }}"
         data-spotlight-group="widget-{{ $key }}">

        <x-card
            :title="$widget['title']"
            :description="$widget['subtitle'] ?? null"
            :padding="true"
            class="flex-1 border-0"
        >
            <x-slot name="actions">
                <span class="dashboard-drag-handle flex h-8 w-8 items-center justify-center rounded-lg text-muted hover:text-primary"
                      aria-label="Drag to reorder {{ $widget['title'] }}" title="Drag to reorder">
                    <x-heroicon-o-bars-2 class="h-4 w-4" />
                </span>
            </x-slot>

            @if ($isChart)
                <div x-data="sigChart(@js($widget))" class="dashboard-chart-wrap relative">
                    <svg x-ref="chart"
                         class="h-64 w-full sm:h-72"
                         role="img"
                         aria-label="{{ $widget['title'] }}"></svg>
                </div>
            @elseif ($widget['type'] === 'feed')
                <ul class="space-y-3" aria-label="{{ $widget['title'] }}">
                    @foreach ($activity as $item)
                        <x-dashboard.activity-item :item="$item" />
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
</div>