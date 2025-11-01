<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        <div class="{{ $trendColor }} text-sm font-medium px-2.5 py-0.5 rounded-full">
            {{ $trend }}
        </div>
    </div>
    <div class="flex items-baseline">
        <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
        <p class="ml-2 text-sm text-gray-500">{{ $unit }}</p>
    </div>
    <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
</div>
