@props(['level' => 'warning', 'items'])

@php
    $tones = [
        'warning' => 'bg-warning/10 border-warning/30 text-warning',
        'danger' => 'bg-error/10 border-error/30 text-error',
        'info' => 'bg-info/10 border-info/30 text-info',
    ];
    $icons = [
        'warning' => '⚠️',
        'danger' => '❌',
        'info' => 'ℹ️',
    ];
@endphp

<div class="{{ $tones[$level] }} mb-4 rounded-lg border p-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <span class="text-lg">{{ $icons[$level] }}</span>
        </div>
        <div class="ml-3 flex-1">
            <div class="text-sm font-medium">
                {{ $title ?? ucfirst($level) . ' Stock Alert' }}
            </div>
            <div class="mt-1 text-sm">
                <p>{{ $slot }}</p>
            </div>
        </div>
    </div>
</div>