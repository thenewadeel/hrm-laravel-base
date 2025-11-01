@props(['items', 'level' => 'warning'])

@php
    $colors = [
        'warning' => 'bg-yellow-50 border-yellow-200',
        'danger' => 'bg-red-50 border-red-200',
        'info' => 'bg-blue-50 border-blue-200',
    ];
    $textColors = [
        'warning' => 'text-yellow-800',
        'danger' => 'text-red-800',
        'info' => 'text-blue-800',
    ];
@endphp

<div class="{{ $colors[$level] }} border rounded-lg p-4 mb-4">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            @if ($level === 'danger')
                <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-red-400" />
            @elseif($level === 'warning')
                <x-heroicon-s-exclamation-circle class="h-5 w-5 text-yellow-400" />
            @else
                <x-heroicon-s-information-circle class="h-5 w-5 text-blue-400" />
            @endif
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium {{ $textColors[$level] }}">
                {{ $title ?? 'Stock Alert' }}
            </h3>
            <div class="mt-2 text-sm {{ $textColors[$level] }}">
                <p>{{ $slot }}</p>
            </div>
        </div>
    </div>
</div>
