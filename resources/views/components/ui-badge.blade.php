@props([
    'variant' => 'solid',
    'color' => 'gray',
    'size' => 'md',
    'icon' => null,
    'dismissible' => false,
    'dot' => false,
])

@php
    $colors = [
        'gray' => [
            'solid' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
            'outline' => 'border-gray-300 text-gray-700 dark:border-gray-600 dark:text-gray-300',
            'subtle' => 'bg-gray-50 text-gray-600 dark:bg-gray-900 dark:text-gray-400'
        ],
        'red' => [
            'solid' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'outline' => 'border-red-300 text-red-700 dark:border-red-600 dark:text-red-300',
            'subtle' => 'bg-red-50 text-red-600 dark:bg-red-900 dark:text-red-400'
        ],
        'yellow' => [
            'solid' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'outline' => 'border-yellow-300 text-yellow-700 dark:border-yellow-600 dark:text-yellow-300',
            'subtle' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400'
        ],
        'green' => [
            'solid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'outline' => 'border-green-300 text-green-700 dark:border-green-600 dark:text-green-300',
            'subtle' => 'bg-green-50 text-green-600 dark:bg-green-900 dark:text-green-400'
        ],
        'blue' => [
            'solid' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'outline' => 'border-blue-300 text-blue-700 dark:border-blue-600 dark:text-blue-300',
            'subtle' => 'bg-blue-50 text-blue-600 dark:bg-blue-900 dark:text-blue-400'
        ],
        'indigo' => [
            'solid' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
            'outline' => 'border-indigo-300 text-indigo-700 dark:border-indigo-600 dark:text-indigo-300',
            'subtle' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-400'
        ],
        'purple' => [
            'solid' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            'outline' => 'border-purple-300 text-purple-700 dark:border-purple-600 dark:text-purple-300',
            'subtle' => 'bg-purple-50 text-purple-600 dark:bg-purple-900 dark:text-purple-400'
        ],
        'pink' => [
            'solid' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
            'outline' => 'border-pink-300 text-pink-700 dark:border-pink-600 dark:text-pink-300',
            'subtle' => 'bg-pink-50 text-pink-600 dark:bg-pink-900 dark:text-pink-400'
        ],
        'orange' => [
            'solid' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            'outline' => 'border-orange-300 text-orange-700 dark:border-orange-600 dark:text-orange-300',
            'subtle' => 'bg-orange-50 text-orange-600 dark:bg-orange-900 dark:text-orange-400'
        ]
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
        'lg' => 'px-3 py-1.5 text-base'
    ];

    $baseClasses = 'inline-flex items-center font-medium rounded-full';
    $colorClasses = $colors[$color][$variant] ?? $colors['gray']['solid'];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    
    if ($variant === 'outline') {
        $baseClasses .= ' border';
    }
@endphp

<span {{ $attributes->merge(['class' => "{$baseClasses} {$colorClasses} {$sizeClasses}"]) }}>
    @if ($dot)
        <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $color === 'green' ? '#10b981' : '#6b7280' }}"></span>
    @endif
    
    @if ($icon)
        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
        </svg>
    @endif
    
    {{ $slot }}
    
    @if ($dismissible)
        <button class="ml-1 p-0.5 rounded-full hover:bg-black hover:bg-opacity-10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-blue-500" type="button">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    @endif
</span>