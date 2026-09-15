@props(['title', 'value', 'trend', 'trendColor', 'description', 'icon' => '📦'])

<div
    class="surface rounded-lg border border-secondary p-6 shadow-sm transition-shadow duration-200 hover:shadow-md">
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center">
            <span class="mr-3 text-2xl">{{ $icon }}</span>
            <h3 class="text-lg font-semibold text-primary">{{ $title }}</h3>
        </div>
        <div class="{{ $trendColor }} rounded-full px-2.5 py-0.5 text-xs font-medium">
            {{ $trend }}
        </div>
    </div>
    <div class="flex items-baseline">
        <p class="text-3xl font-bold text-primary">{{ $value }}</p>
    </div>
    <p class="mt-2 text-sm text-muted">{{ $description }}</p>
</div>