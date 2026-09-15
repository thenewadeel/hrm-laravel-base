@props([
    'type' => 'info',
    'title' => null,
    'description' => null,
    'dismissible' => false,
])

@php
    $tones = [
        'info' => [
            'container' => 'border-info/30 bg-info/10',
            'text' => 'text-info',
        ],
        'success' => [
            'container' => 'border-success/30 bg-success/10',
            'text' => 'text-success',
        ],
        'warning' => [
            'container' => 'border-warning/30 bg-warning/10',
            'text' => 'text-warning',
        ],
        'error' => [
            'container' => 'border-error/30 bg-error/10',
            'text' => 'text-error',
        ],
    ];

    $tone = $tones[$type] ?? $tones['info'];

    $icons = [
        'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z',
        'error' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z',
    ];
@endphp

<div x-data="{ open: true }" x-show="open" x-cloak
    {{ $attributes->merge(['class' => 'rounded-lg border px-4 py-3 '.$tone['container']]) }}
    role="status">
    <div class="flex items-start gap-3">
        <svg class="mt-0.5 size-5 shrink-0 {{ $tone['text'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="{{ $icons[$type] }}" />
        </svg>

        <div class="min-w-0 flex-1 text-sm text-primary">
            @if ($title)
                <p class="font-semibold {{ $tone['text'] }}">{{ $title }}</p>
            @endif
            @if ($description)
                <p class="mt-1 {{ $tone['text'] }}">{{ $description }}</p>
            @endif
            {{ $slot }}
        </div>

        @if ($dismissible)
            <button type="button" x-on:click="open = false"
                class="shrink-0 rounded-md p-1 text-muted transition-colors hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-border-focus"
                aria-label="{{ __('Dismiss') }}">
<x-heroicon-m-x-mark class="size-4" />
            </button>
        @endif
    </div>
</div>