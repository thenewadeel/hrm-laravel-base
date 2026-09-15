@props([
    'label' => null,
    'value' => null,
    'hint' => null,
    'tone' => 'primary',
    'icon' => null,
])

@php
    $tones = [
        'primary' => 'bg-primary/10 text-primary',
        'success' => 'bg-success/10 text-success',
        'warning' => 'bg-warning/10 text-warning',
        'error' => 'bg-error/10 text-error',
        'info' => 'bg-info/10 text-info',
        'muted' => 'bg-tertiary text-muted',
    ];

    $toneClass = $tones[$tone] ?? $tones['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'surface overflow-hidden rounded-lg']) }}>
    <div class="flex items-center gap-4 p-5 sm:p-6">
        @if ($icon)
            <div class="flex-shrink-0 rounded-lg p-3 {{ $toneClass }}">
                <div class="size-6">
                    {{ $icon }}
                </div>
            </div>
        @endif

        <div class="min-w-0 flex-1">
            @if ($label)
                <dt class="truncate text-sm font-medium text-muted">{{ $label }}</dt>
            @endif
            @if ($value)
                <dd class="text-2xl font-semibold leading-tight text-primary">{{ $value }}</dd>
            @endif
            @if ($hint)
                <div class="mt-1 text-xs font-medium {{ $toneClass }}">
                    {{ $hint }}
                </div>
            @endif
        </div>
    </div>
</div>