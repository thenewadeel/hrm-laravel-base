@props(['level' => 'warning', 'items'])

@php
    $colors = [
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'danger' => 'bg-red-50 border-red-200 text-red-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800'
    ];
    $icons = [
        'warning' => '⚠️',
        'danger' => '❌',
        'info' => 'ℹ️'
    ];
@endphp

<div class="{{ $colors[$level] }} border rounded-lg p-4 mb-4">
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