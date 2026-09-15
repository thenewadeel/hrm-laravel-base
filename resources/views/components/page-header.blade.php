@props([
    'title' => null,
    'description' => null,
    'actions' => null,
])

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="min-w-0">
        @if ($title)
            <h2 class="font-semibold text-xl leading-tight text-primary">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-secondary">{{ $description }}</p>
            @endif
        @else
            {{ $slot }}
        @endif
    </div>

    @if ($actions)
        <div class="flex shrink-0 items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>