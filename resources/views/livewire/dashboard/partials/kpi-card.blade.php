@php
    $icon = 'heroicon-o-'.$kpi['icon'];
    $link = (bool) ($kpi['href'] ?? false);
@endphp

@if ($link)
    <a href="{{ $kpi['href'] }}"
       class="group flex items-start gap-3 rounded-xl p-2 -m-1 transition-colors hover:bg-bg-secondary/70 focus:outline-none"
       data-kpi="{{ $kpi['key'] }}">
@else
    <div class="flex items-start gap-3 rounded-xl p-2 -m-1" data-kpi="{{ $kpi['key'] }}">
@endif
        <span class="mt-0.5 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-secondary bg-surface">
            <x-dynamic-component :component="$icon" class="h-5 w-5 {{ match ($kpi['tone']) {
                'error' => 'text-error',
                'warning' => 'text-warning',
                'success' => 'text-success',
                default => 'text-primary',
            } }}" />
        </span>
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-muted">{{ $kpi['label'] }}</p>
            <p class="mt-0.5 truncate text-lg font-semibold text-primary tabular-nums"
               data-kpi-value="{{ $kpi['value'] }}">{{ $kpi['display'] }}</p>
            @if (isset($kpi['delta']) && $kpi['delta'] !== null && $kpi['delta'] !== 0)
                <p class="text-xs font-medium {{ $kpi['delta'] > 0 ? 'text-success' : 'text-error' }}">
                    {{ ($kpi['delta'] > 0 ? '+' : '').$kpi['delta'].'% MoM' }}
                </p>
            @endif
        </div>
@if ($link)
    </a>
@else
    </div>
@endif