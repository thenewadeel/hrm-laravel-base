<div class="bg-white p-6 rounded-lg border border-gray-200 text-center">
    <div class="text-3xl mb-2">{{ $icon }}</div>
    <div class="text-2xl font-bold text-gray-900">{{ $value }}</div>
    <div class="text-sm text-gray-600 mb-2">{{ $title }}</div>
    @if (isset($trend))
        <div
            class="text-xs {{ $trendDirection === 'up' ? 'text-green-600' : 'text-red-600' }} flex items-center justify-center">
            <x-heroicon-s-arrow-{{ $trendDirection }} class="w-3 h-3 mr-1" />
            {{ $trend }}
        </div>
    @endif
</div>
