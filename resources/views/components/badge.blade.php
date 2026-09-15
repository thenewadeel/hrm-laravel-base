@props([
    'variant' => 'solid',
    'color' => 'gray',
    'size' => 'md',
    'icon' => null,
    'dismissible' => false,
    'dot' => false,
    'status' => null, // For status-specific styling
    'wrapper' => false, // For adding relative wrapper div
])

@php
    $colors = [
        'gray' => [
            'solid' => 'bg-bg-tertiary text-muted border-secondary',
            'outline' => 'border-primary text-secondary bg-surface',
            'subtle' => 'bg-tertiary text-muted',
        ],
        'red' => [
            'solid' => 'bg-error/15 text-error border-error/25',
            'outline' => 'border-error/40 text-error bg-surface',
            'subtle' => 'bg-error/10 text-error',
        ],
        'yellow' => [
            'solid' => 'bg-warning/15 text-warning border-warning/25',
            'outline' => 'border-warning/40 text-warning bg-surface',
            'subtle' => 'bg-warning/10 text-warning',
        ],
        'green' => [
            'solid' => 'bg-success/15 text-success border-success/25',
            'outline' => 'border-success/40 text-success bg-surface',
            'subtle' => 'bg-success/10 text-success',
        ],
        'blue' => [
            'solid' => 'bg-info/15 text-info border-info/25',
            'outline' => 'border-info/40 text-info bg-surface',
            'subtle' => 'bg-info/10 text-info',
        ],
        'indigo' => [
            'solid' => 'bg-primary/15 text-primary border-primary/25',
            'outline' => 'border-primary text-primary bg-surface',
            'subtle' => 'bg-primary/10 text-primary',
        ],
        'purple' => [
            'solid' => 'bg-primary/15 text-primary border-primary/25',
            'outline' => 'border-primary text-primary bg-surface',
            'subtle' => 'bg-primary/10 text-primary',
        ],
        'pink' => [
            'solid' => 'bg-error/15 text-error border-error/25',
            'outline' => 'border-error/40 text-error bg-surface',
            'subtle' => 'bg-error/10 text-error',
        ],
        'orange' => [
            'solid' => 'bg-warning/15 text-warning border-warning/25',
            'outline' => 'border-warning/40 text-warning bg-surface',
            'subtle' => 'bg-warning/10 text-warning',
        ],
    ];

    // Status-specific color mapping
    $statusColors = [
        'active' => 'green',
        'inactive' => 'gray',
        'pending' => 'yellow',
        'completed' => 'blue',
        'failed' => 'red',
        'cancelled' => 'gray',
        'approved' => 'green',
        'rejected' => 'red',
        'draft' => 'gray',
        'published' => 'green',
        'archived' => 'gray',
        'urgent' => 'red',
        'high' => 'orange',
        'medium' => 'yellow',
        'low' => 'blue',
    ];

    $sizes = [
        'xs' => 'px-1.5 py-0.5 text-xs',
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
        'lg' => 'px-3 py-1.5 text-base',
        'xl' => 'px-4 py-2 text-lg',
    ];

    // Determine color based on status if provided
    $finalColor = $status ? ($statusColors[$status] ?? $color) : $color;

    $baseClasses = 'inline-flex items-center font-medium rounded-full transition-colors duration-200';
    $colorClasses = $colors[$finalColor][$variant] ?? $colors['gray']['solid'];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];

    if ($variant === 'outline') {
        $baseClasses .= ' border';
    }

    $finalClasses = ($wrapper ? 'absolute' : 'inline-flex') . " items-center font-medium rounded-full transition-colors duration-200 {$colorClasses} {$sizeClasses}";

    $dotColors = [
        'green' => 'bg-success',
        'red' => 'bg-error',
        'yellow' => 'bg-warning',
        'orange' => 'bg-warning',
        'gray' => 'bg-text-muted',
    ];
    $dotClass = $dotColors[$finalColor] ?? 'bg-text-muted';
@endphp

@if ($wrapper)
    <div class="relative">
@endif

<span {{ $attributes->merge(['class' => $finalClasses]) }}>
    @if ($dot)
        <span class="h-2 w-2 rounded-full {{ $wrapper ? '' : 'mr-2' }} {{ $dotClass }}"></span>
    @endif

    @if ($icon)
        <svg class="h-4 w-4 {{ $wrapper ? '' : 'mr-1' }} flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            @if ($icon === 'check')
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            @elseif ($icon === 'x')
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            @elseif ($icon === 'alert')
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            @elseif ($icon === 'info')
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd" />
            @else
                {{-- Default user icon --}}
                <path fill-rule="evenodd"
                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            @endif
        </svg>
    @endif

    {{ $slot ?? '' }}

    @if ($dismissible)
        <button
            class="ml-1 rounded-full p-0.5 hover:bg-black hover:bg-opacity-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-border-focus dark:hover:bg-white dark:hover:bg-opacity-10"
            type="button">
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    @endif
</span>

@if ($wrapper)
    </div>
@endif