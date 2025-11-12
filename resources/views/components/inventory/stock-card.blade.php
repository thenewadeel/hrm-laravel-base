@props(['title', 'value', 'trend', 'trendColor', 'description', 'icon' => '📦'])

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <span class="text-2xl mr-3">{{ $icon }}</span>
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        </div>
        <div class="{{ $trendColor }} text-xs font-medium px-2.5 py-0.5 rounded-full">
            {{ $trend }}
        </div>
    </div>
    <div class="flex items-baseline">
        <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
    </div>
    <p class="mt-2 text-sm text-gray-500">{{ $description }}</p>
</div>