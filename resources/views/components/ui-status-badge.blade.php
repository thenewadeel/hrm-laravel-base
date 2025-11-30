@php
    $label = $customLabel ?? ucfirst($status);
    $color = $customLabel ? 'green' : (match($status) {
        'active', 'completed', 'success' => 'green',
        'inactive', 'failed', 'error' => 'red',
        'pending', 'warning' => 'yellow',
        'processing', 'info' => 'blue',
        default => 'gray'
    });
@endphp
<x-ui-badge color="{{ $color }}" size="sm">
    {{ $label }}
</x-ui-badge>