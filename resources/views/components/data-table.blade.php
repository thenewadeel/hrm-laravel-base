@props([
    'headers' => [],
    'data' => [],
    'emptyMessage' => 'No data found',
    'loading' => false,
    'rowClick' => null,
])

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    @if ($loading)
        <div class="p-6 space-y-4 animate-pulse">
            <div class="h-6 bg-gray-200 rounded w-1/4"></div>
            <div class="space-y-3">
                @foreach (range(1, 5) as $i)
                    <div class="h-4 bg-gray-200 rounded {{ $i % 2 ? 'w-5/6' : 'w-4/6' }}"></div>
                @endforeach
            </div>
        </div>
    @elseif(empty($data))
        <x-empty-state title="No data available" :description="$emptyMessage" icon="document" />
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach ($headers as $header)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    @endif
</div>
