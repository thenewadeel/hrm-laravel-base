@props([
    'status',
    'size' => 'md',
    'showIcon' => true,
    'customLabel' => null,
])

@php
    $statusConfig = [
        // General Status
        'active' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'check-circle',
            'label' => 'Active',
        ],
        'inactive' => [
            'color' => 'gray',
            'variant' => 'subtle',
            'icon' => 'minus-circle',
            'label' => 'Inactive',
        ],
        'pending' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'clock',
            'label' => 'Pending',
        ],
        'draft' => [
            'color' => 'gray',
            'variant' => 'outline',
            'icon' => 'document-text',
            'label' => 'Draft',
        ],
        
        // Financial Status
        'posted' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'check',
            'label' => 'Posted',
        ],
        'unposted' => [
            'color' => 'yellow',
            'variant' => 'outline',
            'icon' => 'document',
            'label' => 'Unposted',
        ],
        'void' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'x-circle',
            'label' => 'Void',
        ],
        'reconciled' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'check-badge',
            'label' => 'Reconciled',
        ],
        'unreconciled' => [
            'color' => 'yellow',
            'variant' => 'subtle',
            'icon' => 'exclamation-triangle',
            'label' => 'Unreconciled',
        ],
        
        // HR Status
        'present' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'check-circle',
            'label' => 'Present',
        ],
        'absent' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'x-circle',
            'label' => 'Absent',
        ],
        'leave' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'calendar-days',
            'label' => 'On Leave',
        ],
        'holiday' => [
            'color' => 'purple',
            'variant' => 'solid',
            'icon' => 'gift',
            'label' => 'Holiday',
        ],
        
        // Inventory Status
        'in_stock' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'cube',
            'label' => 'In Stock',
        ],
        'low_stock' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'exclamation-triangle',
            'label' => 'Low Stock',
        ],
        'out_of_stock' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'x-circle',
            'label' => 'Out of Stock',
        ],
        'discontinued' => [
            'color' => 'gray',
            'variant' => 'subtle',
            'icon' => 'archive-box',
            'label' => 'Discontinued',
        ],
        
        // System Status
        'online' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'signal',
            'label' => 'Online',
        ],
        'offline' => [
            'color' => 'gray',
            'variant' => 'subtle',
            'icon' => 'signal-slash',
            'label' => 'Offline',
        ],
        'error' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'x-circle',
            'label' => 'Error',
        ],
        'warning' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'exclamation-triangle',
            'label' => 'Warning',
        ],
        'success' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'check-circle',
            'label' => 'Success',
        ],
        'info' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'information-circle',
            'label' => 'Info',
        ],
        
        // Financial Year Status
        'closing' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'archive-box-arrow-down',
            'label' => 'Closing',
        ],
        'closed' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'lock-closed',
            'label' => 'Closed',
        ],
        'locked' => [
            'color' => 'orange',
            'variant' => 'solid',
            'icon' => 'lock-closed',
            'label' => 'Locked',
        ],
        'unlocked' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'lock-open',
            'label' => 'Unlocked',
        ],
    ];

    $config = $statusConfig[$status] ?? [
        'color' => 'gray',
        'variant' => 'subtle',
        'icon' => 'question-mark-circle',
        'label' => ucfirst($status),
    ];
@endphp

<x-badge 
    :color="$config['color']"
    :variant="$config['variant']"
    :size="$size"
    {{ $attributes }}
>
    {{ $customLabel ?? $config['label'] }}
</x-new-badge>