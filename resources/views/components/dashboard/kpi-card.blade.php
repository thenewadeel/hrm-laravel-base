@props([
    'kpi' => null,
])

@php
    $icon = 'heroicon-o-'.($kpi['icon'] ?? 'sparkles');
    $href = $kpi['href'] ?? null;
    $delta = $kpi['delta'] ?? null;
    $toneClass = match ($kpi['tone'] ?? 'primary') {
        'error' => 'text-error',
        'warning' => 'text-warning',
        'success' => 'text-success',
        default => 'text-primary',
    };
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    class="group flex items-start gap-3 rounded-xl p-2 -m-1 transition-colors hover:bg-bg-secondary/70 focus:outline-none"
    data-kpi="{{ $kpi['key'] }}">
    <span class="mt-0.5 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-secondary bg-surface">
        <x-dynamic-component :component="$icon" class="h-5 w-5 {{ $toneClass }}" />
    </span>
    <div class="min-w-0">
        <p class="text-xs font-medium uppercase tracking-wide text-muted">{{ $kpi['label'] }}</p>
        <p class="mt-0.5 truncate text-lg font-semibold text-primary tabular-nums"
           data-kpi-value="{{ $kpi['value'] }}">{{ $kpi['display'] }}</p>
        @if ($delta !== null && $delta !== 0)
            <p class="text-xs font-medium {{ $delta > 0 ? 'text-success' : 'text-error' }}">
                {{ ($delta > 0 ? '+' : '').$delta.'% MoM' }}
            </p>
        @endif
    </div>
</{{ $tag }}>