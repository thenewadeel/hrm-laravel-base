@props([
    'item' => null,
])

@php
    $icon = match ($item['type'] ?? null) {
        'transaction' => 'heroicon-o-arrows-right-left',
        'voucher' => 'heroicon-o-receipt-percent',
        'member' => 'heroicon-o-user-plus',
        default => 'heroicon-o-sparkles',
    };
@endphp

<li>
    <a href="{{ $item['href'] }}" class="group flex items-center gap-3 rounded-lg p-2 -m-2 transition-colors hover:bg-bg-secondary/70">
        <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-secondary">
            <x-dynamic-component :component="$icon" class="h-4 w-4 text-primary" />
        </span>
        <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-medium text-primary">{{ $item['title'] }}</span>
            <span class="block truncate text-xs text-muted">{{ $item['meta'] }}</span>
        </span>
        <span class="flex-shrink-0 text-xs text-muted">{{ $item['time'] }}</span>
    </a>
</li>