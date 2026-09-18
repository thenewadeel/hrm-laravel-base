@php
    $chartTypes = ['area', 'bars', 'donut', 'spark', 'gauge'];
    $isChart = in_array($widget['type'], $chartTypes, true);
    $span = match ($widget['type']) {
        'area' => 'lg:col-span-2',
        'feed' => 'lg:col-span-2',
        'bars' => 'lg:col-span-1',
        default => 'lg:col-span-1',
    };
@endphp

<div wire:key="widget-{{ $key }}"
     class="dashboard-widget {{ $span }}"
     data-widget-key="{{ $key }}"
     draggable="true">
    <div class="dashboard-widget-inner surface h-full flex flex-col overflow-hidden rounded-2xl border border-secondary"
         data-widget="{{ $key }}"
         data-spotlight-group="widget-{{ $key }}">

        <div class="flex items-start justify-between gap-3 border-b border-secondary px-5 py-4"
             data-widget-handle>
            <div class="min-w-0">
                <h3 class="truncate text-sm font-semibold text-primary">{{ $widget['title'] }}</h3>
                @if (! empty($widget['subtitle']))
                    <p class="mt-0.5 truncate text-xs text-muted">{{ $widget['subtitle'] }}</p>
                @endif
            </div>
            <div class="flex items-center gap-1">
                <span class="dashboard-drag-handle flex h-8 w-8 items-center justify-center rounded-lg text-muted hover:text-primary"
                      aria-label="Drag to reorder {{ $widget['title'] }}" title="Drag to reorder">
                    <x-heroicon-o-bars-2 class="h-4 w-4" />
                </span>
            </div>
        </div>

        <div class="flex-1 px-5 py-4">
            @if ($isChart)
                <div x-data="sigChart(@js($widget))" class="dashboard-chart-wrap relative h-full w-full">
                    <svg x-ref="chart"
                         class="h-64 w-full sm:h-72"
                         role="img"
                         aria-label="{{ $widget['title'] }}"></svg>
                </div>
            @elseif ($widget['type'] === 'feed')
                <ul class="space-y-3" aria-label="{{ $widget['title'] }}">
                    @foreach ($activity as $item)
                        <li>
                            <a href="{{ $item['href'] }}" class="group flex items-center gap-3 rounded-lg p-2 -m-2 transition-colors hover:bg-bg-secondary/70">
                                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-secondary">
                                    <x-dynamic-component :component="'heroicon-o-'.match ($item['type']) {
                                        'transaction' => 'arrows-right-left',
                                        'voucher' => 'receipt-percent',
                                        'member' => 'user-plus',
                                        default => 'sparkles',
                                    }" class="h-4 w-4 text-primary" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-primary">{{ $item['title'] }}</span>
                                    <span class="block truncate text-xs text-muted">{{ $item['meta'] }}</span>
                                </span>
                                <span class="flex-shrink-0 text-xs text-muted">{{ $item['time'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>