@props([
    'type',
    'size' => 'md',
    'showIcon' => true,
    'showDot' => false,
    'customLabel' => null,
])

@php
    $typeConfig = [
        // Document Types
        'pdf' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'document-text',
            'label' => 'PDF',
        ],
        'excel' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'chart-bar',
            'label' => 'Excel',
        ],
        'word' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'document',
            'label' => 'Word',
        ],
        'image' => [
            'color' => 'purple',
            'variant' => 'solid',
            'icon' => 'photo',
            'label' => 'Image',
        ],
        'video' => [
            'color' => 'pink',
            'variant' => 'solid',
            'icon' => 'video-camera',
            'label' => 'Video',
        ],
        'audio' => [
            'color' => 'indigo',
            'variant' => 'solid',
            'icon' => 'musical-note',
            'label' => 'Audio',
        ],
        'archive' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'archive-box',
            'label' => 'Archive',
        ],
        'text' => [
            'color' => 'gray',
            'variant' => 'solid',
            'icon' => 'document-text',
            'label' => 'Text',
        ],
        
        // Data Types
        'string' => [
            'color' => 'blue',
            'variant' => 'outline',
            'icon' => 'at-symbol',
            'label' => 'String',
        ],
        'number' => [
            'color' => 'green',
            'variant' => 'outline',
            'icon' => 'hashtag',
            'label' => 'Number',
        ],
        'boolean' => [
            'color' => 'purple',
            'variant' => 'outline',
            'icon' => 'toggle',
            'label' => 'Boolean',
        ],
        'date' => [
            'color' => 'indigo',
            'variant' => 'outline',
            'icon' => 'calendar',
            'label' => 'Date',
        ],
        'datetime' => [
            'color' => 'indigo',
            'variant' => 'solid',
            'icon' => 'calendar-days',
            'label' => 'DateTime',
        ],
        'email' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'envelope',
            'label' => 'Email',
        ],
        'url' => [
            'color' => 'cyan',
            'variant' => 'solid',
            'icon' => 'link',
            'label' => 'URL',
        ],
        'phone' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'phone',
            'label' => 'Phone',
        ],
        'currency' => [
            'color' => 'emerald',
            'variant' => 'solid',
            'icon' => 'currency-dollar',
            'label' => 'Currency',
        ],
        'percentage' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'percent-badge',
            'label' => 'Percentage',
        ],
        
        // API/Response Types
        'json' => [
            'color' => 'gray',
            'variant' => 'solid',
            'icon' => 'code-bracket',
            'label' => 'JSON',
        ],
        'xml' => [
            'color' => 'orange',
            'variant' => 'solid',
            'icon' => 'code-bracket-square',
            'label' => 'XML',
        ],
        'html' => [
            'color' => 'orange',
            'variant' => 'outline',
            'icon' => 'code-bracket',
            'label' => 'HTML',
        ],
        'css' => [
            'color' => 'blue',
            'variant' => 'outline',
            'icon' => 'paint-brush',
            'label' => 'CSS',
        ],
        'javascript' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'code-bracket',
            'label' => 'JavaScript',
        ],
        
        // File Status Types
        'uploaded' => [
            'color' => 'green',
            'variant' => 'solid',
            'icon' => 'cloud-arrow-up',
            'label' => 'Uploaded',
        ],
        'downloading' => [
            'color' => 'blue',
            'variant' => 'solid',
            'icon' => 'cloud-arrow-down',
            'label' => 'Downloading',
        ],
        'processing' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'cog',
            'label' => 'Processing',
        ],
        'failed' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'x-circle',
            'label' => 'Failed',
        ],
    ];

    $config = $typeConfig[$type] ?? [
        'color' => 'gray',
        'variant' => 'subtle',
        'icon' => 'question-mark-circle',
        'label' => ucfirst($type),
    ];
@endphp

<x-new-badge 
    :color="$config['color']"
    :variant="$config['variant']"
    :size="$size"
    :icon="$showIcon ? $config['icon'] : null"
    :dot="$showDot"
    {{ $attributes }}
>
    {{ $customLabel ?? $config['label'] }}
</x-new-badge>