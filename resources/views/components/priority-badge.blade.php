@props([
    'priority',
    'size' => 'md',
    'showIcon' => true,
    'showLabel' => true,
    'customLabel' => null,
])

@php
    $priorityConfig = [
        'low' => [
            'color' => 'gray',
            'variant' => 'subtle',
            'icon' => 'arrow-down',
            'label' => 'Low',
            'weight' => 1,
        ],
        'medium' => [
            'color' => 'yellow',
            'variant' => 'solid',
            'icon' => 'minus',
            'label' => 'Medium',
            'weight' => 2,
        ],
        'high' => [
            'color' => 'orange',
            'variant' => 'solid',
            'icon' => 'arrow-up',
            'label' => 'High',
            'weight' => 3,
        ],
        'critical' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'exclamation-triangle',
            'label' => 'Critical',
            'weight' => 4,
        ],
        'urgent' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'fire',
            'label' => 'Urgent',
            'weight' => 5,
        ],
        'emergency' => [
            'color' => 'red',
            'variant' => 'solid',
            'icon' => 'shield-exclamation',
            'label' => 'Emergency',
            'weight' => 6,
        ],
    ];

    $config = $priorityConfig[$priority] ?? [
        'color' => 'gray',
        'variant' => 'subtle',
        'icon' => 'question-mark-circle',
        'label' => 'Unknown',
        'weight' => 0,
    ];
@endphp

@if($showLabel)
    <x-new-badge 
        :color="$config['color']"
        :variant="$config['variant']"
        :size="$size"
        {{ $attributes }}
    >
        {{ $customLabel ?? $config['label'] }}
    </x-new-badge>
@else
    <span 
        {{ $attributes->merge([
            'class' => "inline-flex items-center justify-center w-3 h-3 rounded-full bg-{$config['color']}-500",
            'title' => $config['label']
        ]) }}
    >

    </span>
@endif