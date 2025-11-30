@props([
    'variant' => 'solid',
    'color' => 'gray',
    'size' => 'md',
    'rounded' => 'full',
    'icon' => null,
    'iconPosition' => 'left',
    'dismissible' => false,
    'dot' => false,
])

@php
    $colorVariants = [
        'solid' => [
            'gray' => [
                'light' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                'medium' => 'bg-gray-200 text-gray-900 dark:bg-gray-600 dark:text-gray-100',
                'dark' => 'bg-gray-800 text-white dark:bg-gray-900 dark:text-gray-100',
            ],
            'red' => [
                'light' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
                'medium' => 'bg-red-200 text-red-900 dark:bg-red-800/50 dark:text-red-100',
                'dark' => 'bg-red-600 text-white dark:bg-red-500 dark:text-white',
            ],
            'yellow' => [
                'light' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
                'medium' => 'bg-yellow-200 text-yellow-900 dark:bg-yellow-800/50 dark:text-yellow-100',
                'dark' => 'bg-yellow-600 text-white dark:bg-yellow-500 dark:text-white',
            ],
            'green' => [
                'light' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
                'medium' => 'bg-green-200 text-green-900 dark:bg-green-800/50 dark:text-green-100',
                'dark' => 'bg-green-600 text-white dark:bg-green-500 dark:text-white',
            ],
            'blue' => [
                'light' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200',
                'medium' => 'bg-blue-200 text-blue-900 dark:bg-blue-800/50 dark:text-blue-100',
                'dark' => 'bg-blue-600 text-white dark:bg-blue-500 dark:text-white',
            ],
            'indigo' => [
                'light' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-200',
                'medium' => 'bg-indigo-200 text-indigo-900 dark:bg-indigo-800/50 dark:text-indigo-100',
                'dark' => 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white',
            ],
            'purple' => [
                'light' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-200',
                'medium' => 'bg-purple-200 text-purple-900 dark:bg-purple-800/50 dark:text-purple-100',
                'dark' => 'bg-purple-600 text-white dark:bg-purple-500 dark:text-white',
            ],
            'pink' => [
                'light' => 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-200',
                'medium' => 'bg-pink-200 text-pink-900 dark:bg-pink-800/50 dark:text-pink-100',
                'dark' => 'bg-pink-600 text-white dark:bg-pink-500 dark:text-white',
            ],
        ],
        'outline' => [
            'gray' => 'border border-gray-300 text-gray-700 dark:border-gray-600 dark:text-gray-300 bg-transparent',
            'red' => 'border border-red-300 text-red-700 dark:border-red-600 dark:text-red-300 bg-transparent',
            'yellow' => 'border border-yellow-300 text-yellow-700 dark:border-yellow-600 dark:text-yellow-300 bg-transparent',
            'green' => 'border border-green-300 text-green-700 dark:border-green-600 dark:text-green-300 bg-transparent',
            'blue' => 'border border-blue-300 text-blue-700 dark:border-blue-600 dark:text-blue-300 bg-transparent',
            'indigo' => 'border border-indigo-300 text-indigo-700 dark:border-indigo-600 dark:text-indigo-300 bg-transparent',
            'purple' => 'border border-purple-300 text-purple-700 dark:border-purple-600 dark:text-purple-300 bg-transparent',
            'pink' => 'border border-pink-300 text-pink-700 dark:border-pink-600 dark:text-pink-300 bg-transparent',
        ],
        'subtle' => [
            'gray' => 'bg-gray-50 text-gray-600 dark:bg-gray-800/50 dark:text-gray-400',
            'red' => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
            'yellow' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
            'green' => 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400',
            'blue' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
            'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
            'purple' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
            'pink' => 'bg-pink-50 text-pink-600 dark:bg-pink-900/20 dark:text-pink-400',
        ],
    ];

    $sizes = [
        'xs' => 'px-1.5 py-0.5 text-xs',
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-0.5 text-sm',
        'lg' => 'px-3 py-1 text-sm',
        'xl' => 'px-4 py-1.5 text-base',
    ];

    $roundedOptions = [
        'none' => 'rounded-none',
        'sm' => 'rounded-sm',
        'md' => 'rounded-md',
        'lg' => 'rounded-lg',
        'full' => 'rounded-full',
    ];

    $baseClasses = 'inline-flex items-center font-medium transition-colors duration-200';
    
    // Get color classes based on variant
    $colorClasses = '';
    if ($variant === 'solid') {
        $shade = $attributes->get('shade', 'light');
        $colorClasses = $colorVariants['solid'][$color][$shade] ?? $colorVariants['solid']['gray']['light'];
    } else {
        $colorClasses = $colorVariants[$variant][$color] ?? $colorVariants[$variant]['gray'];
    }
    
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    $roundedClasses = $roundedOptions[$rounded] ?? $roundedOptions['full'];
    
    $classes = "{$baseClasses} {$colorClasses} {$sizeClasses} {$roundedClasses}";
    
    if ($dot) {
        $classes .= ' pl-2.5';
    }
@endphp

@if($dismissible)
    <div {{ $attributes->merge(['class' => $classes]) }}>
        @if($dot)
            <span class="w-2 h-2 mr-1.5 rounded-full bg-current opacity-60"></span>
        @endif
        <span>{{ $slot }}</span>
        <button 
            type="button" 
            class="ml-1.5 inline-flex items-center justify-center p-0.5 rounded-full hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
            @click="$emit('dismiss')"
        >
            ×
        </button>
    </div>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        @if($dot)
            <span class="w-2 h-2 mr-1.5 rounded-full bg-current opacity-60"></span>
        @endif
        {{ $slot }}
    </span>
@endif