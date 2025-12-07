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
            'solid' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 border-gray-200 dark:border-gray-700',
            'outline' => 'border-gray-300 text-gray-700 dark:border-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800',
            'subtle' => 'bg-gray-50 text-gray-600 dark:bg-gray-900 dark:text-gray-400'
        ],
        'red' => [
            'solid' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border-red-200 dark:border-red-700',
            'outline' => 'border-red-300 text-red-700 dark:border-red-600 dark:text-red-300 bg-white dark:bg-gray-800',
            'subtle' => 'bg-red-50 text-red-600 dark:bg-red-900 dark:text-red-400'
        ],
        'yellow' => [
            'solid' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border-yellow-200 dark:border-yellow-700',
            'outline' => 'border-yellow-300 text-yellow-700 dark:border-yellow-600 dark:text-yellow-300 bg-white dark:bg-gray-800',
            'subtle' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400'
        ],
        'green' => [
            'solid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-green-200 dark:border-green-700',
            'outline' => 'border-green-300 text-green-700 dark:border-green-600 dark:text-green-300 bg-white dark:bg-gray-800',
            'subtle' => 'bg-green-50 text-green-600 dark:bg-green-900 dark:text-green-400'
        ],
        'blue' => [
            'solid' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 border-blue-200 dark:border-blue-700',
            'outline' => 'border-blue-300 text-blue-700 dark:border-blue-600 dark:text-blue-300 bg-white dark:bg-gray-800',
            'subtle' => 'bg-blue-50 text-blue-600 dark:bg-blue-900 dark:text-blue-400'
        ],
        'indigo' => [
            'solid' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 border-indigo-200 dark:border-indigo-700',
            'outline' => 'border-indigo-300 text-indigo-700 dark:border-indigo-600 dark:text-indigo-300 bg-white dark:bg-gray-800',
            'subtle' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-400'
        ],
        'purple' => [
            'solid' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 border-purple-200 dark:border-purple-700',
            'outline' => 'border-purple-300 text-purple-700 dark:border-purple-600 dark:text-purple-300 bg-white dark:bg-gray-800',
            'subtle' => 'bg-purple-50 text-purple-600 dark:bg-purple-900 dark:text-purple-400'
        ],
        'pink' => [
            'solid' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200 border-pink-200 dark:border-pink-700',
            'outline' => 'border-pink-300 text-pink-700 dark:border-pink-600 dark:text-pink-300 bg-white dark:bg-gray-800',
            'subtle' => 'bg-pink-50 text-pink-600 dark:bg-pink-900 dark:text-pink-400'
        ],
        'orange' => [
            'solid' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 border-orange-200 dark:border-orange-700',
            'outline' => 'border-orange-300 text-orange-700 dark:border-orange-600 dark:text-orange-300 bg-white dark:bg-gray-800',
            'subtle' => 'bg-orange-50 text-orange-600 dark:bg-orange-900 dark:text-orange-400'
        ]
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
        'xl' => 'px-4 py-2 text-lg'
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
@endphp

@if ($wrapper)
    <div class="relative">
@endif

<span {{ $attributes->merge(['class' => $finalClasses]) }}>
    @if ($dot)
        <span class="w-2 h-2 rounded-full {{ $wrapper ? '' : 'mr-2' }}" 
              style="background-color: {{ $finalColor === 'green' ? '#10b981' : ($finalColor === 'red' ? '#ef4444' : ($finalColor === 'yellow' ? '#f59e0b' : '#6b7280')) }}">
        </span>
    @endif
    
    @if ($icon)
        <svg class="w-4 h-4 {{ $wrapper ? '' : 'mr-1' }} flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            @if ($icon === 'check')
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            @elseif ($icon === 'x')
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            @elseif ($icon === 'alert')
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            @elseif ($icon === 'info')
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            @else
                <!-- Default user icon -->
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
            @endif
        </svg>
    @endif
    
    {{ $slot ?? '' }}
    
    @if ($dismissible)
        <button class="ml-1 p-0.5 rounded-full hover:bg-black hover:bg-opacity-10 dark:hover:bg-white dark:hover:bg-opacity-10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 focus:ring-blue-500" type="button">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    @endif
</span>

@if ($wrapper)
    </div>
@endif