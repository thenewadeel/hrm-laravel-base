@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'padding' => true,
    'elevated' => false,
    'header' => null,
    'actions' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'surface overflow-hidden'.($elevated ? ' surface-elevated' : '')]) }}>
    @if ($title || $description || $icon || $header || $actions)
        <div class="flex items-center justify-between gap-4 border-b border-secondary px-5 py-4">
            <div class="flex min-w-0 items-center gap-3">
                @if ($icon)
                    <div class="flex-shrink-0 text-accent">
                        <div class="size-6">
                            {{ $icon }}
                        </div>
                    </div>
                @endif
                <div class="min-w-0">
                    @if ($title)
                        <h3 class="text-lg font-semibold leading-6 text-primary">{{ $title }}</h3>
                    @endif
                    @if ($description)
                        <p class="mt-0.5 text-sm text-secondary">{{ $description }}</p>
                    @endif
                    @if ($header)
                        <div class="mt-2">{{ $header }}</div>
                    @endif
                </div>
            </div>
            @if ($actions)
                <div class="flex shrink-0 items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    @if ($padding)
        <div class="px-5 py-5 sm:px-6">{{ $slot }}</div>
    @else
        <div>{{ $slot }}</div>
    @endif

    @if ($footer)
        <div class="border-t border-secondary px-5 py-4">{{ $footer }}</div>
    @endif
</div>