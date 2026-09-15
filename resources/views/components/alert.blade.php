@props([
    'type' => 'info',
    'title' => null,
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
            {{ $slot }}
        </div>

        @if ($dismissible)
            <button type="button" x-on:click="open = false"
                class="shrink-0 rounded-md p-1 text-muted transition-colors hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-border-focus"
                aria-label="{{ __('Dismiss') }}">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        @endif
    </div>
</div>