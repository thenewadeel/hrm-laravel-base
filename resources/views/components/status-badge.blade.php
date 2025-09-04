@props([
    'status' => 'idle', // idle, loading, success, error, warning
    'text' => null,
])

@php
    $statusConfig = [
        'idle' => ['bg-gray-200', 'text-gray-800', 'Ready'],
        'loading' => ['bg-blue-200', 'text-blue-800', 'Loading...'],
        'success' => ['bg-green-200', 'text-green-800', 'Success'],
        'error' => ['bg-red-200', 'text-red-800', 'Error'],
        'warning' => ['bg-yellow-200', 'text-yellow-800', 'Warning'],
    ];

    [$bgColor, $textColor, $defaultText] = $statusConfig[$status] ?? $statusConfig['idle'];
    $displayText = $text ?? $defaultText;
@endphp

<span {{ $attributes->merge(['class' => "px-3 py-1 rounded-full text-sm font-medium {$bgColor} {$textColor}"]) }}>
    {{ $displayText }}
</span>
