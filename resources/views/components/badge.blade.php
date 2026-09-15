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
        @if ($icon === 'check')
            <x-heroicon-m-check class="h-4 w-4 {{ $wrapper ? '' : 'mr-1' }} flex-shrink-0" />
        @elseif ($icon === 'x')
            <x-heroicon-m-x-mark class="h-4 w-4 {{ $wrapper ? '' : 'mr-1' }} flex-shrink-0" />
        @elseif ($icon === 'alert')
            <x-heroicon-m-exclamation-triangle class="h-4 w-4 {{ $wrapper ? '' : 'mr-1' }} flex-shrink-0" />
        @elseif ($icon === 'info')
            <x-heroicon-m-information-circle class="h-4 w-4 {{ $wrapper ? '' : 'mr-1' }} flex-shrink-0" />
        @else
            <x-heroicon-m-user class="h-4 w-4 {{ $wrapper ? '' : 'mr-1' }} flex-shrink-0" />
        @endif
    @endif

    {{ $slot ?? '' }}

    @if ($dismissible)
        <button
            class="ml-1 rounded-full p-0.5 hover:bg-black hover:bg-opacity-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-border-focus dark:hover:bg-white dark:hover:bg-opacity-10"
            type="button">
            <x-heroicon-m-x-mark class="h-3 w-3" />
        </button>
    @endif
</span>

@if ($wrapper)
    </div>
@endif