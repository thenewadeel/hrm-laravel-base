@props([
    'title' => '',
    'description' => '',
    'icon' => null,
    'color' => 'blue', // blue, green, red, yellow, purple, gray
    'progress' => null, // 0-100
    'trend' => null, // up, down, neutral
    'trendValue' => null,
])

@php
$colorClasses = [
    'blue' => 'bg-blue-500 text-white',
    'green' => 'bg-green-500 text-white',
    'red' => 'bg-red-500 text-white',
    'yellow' => 'bg-yellow-500 text-white',
    'purple' => 'bg-purple-500 text-white',
    'gray' => 'bg-gray-500 text-white',
];

$trendIcons = [
    'up' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0l-4-4m4 4l-4 4m6-4H9a2 2 0 00-2 2v6a2 2 0 002 2h8a2 2 0 002-2V9a2 2 0 00-2-2z" />',
    'down' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0l-4 4m4-4l-4-4m6 4H9a2 2 0 00-2-2v-6a2 2 0 002-2h8a2 2 0 002 2v6a2 2 0 002 2z" />',
    'neutral' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2V3a2 2 0 00-2-2H9a2 2 0 00-2 2v10z" />',
];

$bgColorClass = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
    <div class="p-6">
        <div class="flex items-center">
            @if($icon)
                <div class="flex-shrink-0 {{ $bgColorClass }} p-3 rounded-lg">
                    <span class="text-lg">{{ $icon }}</span>
                </div>
            @endif
            
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        {{ $title }}
                    </dt>
                    
                    @if($trend && $trendValue)
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $slot }}
                            </div>
                            
                            <div class="ml-2 flex items-baseline text-sm font-semibold {{ $trend === 'up' ? 'text-green-600' : ($trend === 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $trendIcons[$trend] !!}
                                </svg>
                                
                                {{ $trendValue }}
                            </div>
                        </dd>
                    @else
                        <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ $slot }}
                        </dd>
                    @endif
                </dl>
            </div>
        </div>
        
        @if($progress !== null)
            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif
        
        @if($description)
            <div class="mt-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $description }}
                </div>
            </div>
        @endif
    </div>
    </div>
</div>