@props([
    'headers' => [],
    'data' => [],
    'emptyMessage' => 'No data found',
    'loading' => false,
    'sortBy' => null,
    'sortDirection' => 'asc',
])

<div x-data="{
    data: {{ json_encode($data) }},
    sortBy: '{{ $sortBy }}',
    sortDirection: '{{ $sortDirection }}',

    sort(column) {
        if (this.sortBy === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortBy = column;
            this.sortDirection = 'asc';
        }

        this.data.sort((a, b) => {
            let aVal = a[this.sortBy];
            let bVal = b[this.sortBy];

            if (typeof aVal === 'string') {
                return this.sortDirection === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            } else {
                return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
            }
        });
    }
}" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
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
                        @foreach ($headers as $key => $header)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
                                @click="sort('{{ $key }}')">
                                <div class="flex items-center space-x-1">
                                    <span>{{ $header }}</span>
                                    <template x-if="sortBy === '{{ $key }}'">
                                        <template x-if="sortDirection === 'asc'">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        </template>
                                        <template x-if="sortDirection === 'desc'">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </template>
                                    </template>
                                </div>
                            </th>
                        @endforeach
                        {{ $customHeader ?? '' }}
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="item in data" :key="item.id">
                        <tr>
                            @foreach (array_keys($headers) as $key)
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                    x-text="item['{{ $key }}']"></td>
                            @endforeach
                            {{ $customColumns ?? '' }}
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    @endif
</div>
