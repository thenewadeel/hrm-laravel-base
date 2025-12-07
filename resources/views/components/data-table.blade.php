@props([
    'headers' => [],
    'data' => [],
    'emptyMessage' => 'No data found',
    'loading' => false,
    'sortBy' => null,
    'sortDirection' => 'asc',
    'columnTypes' => [],
    'currencySymbol' => 'Rs. ', // Default value
])

<div x-data="{
    data: {{ json_encode($data) }},
    sortBy: '{{ $sortBy }}',
    sortDirection: '{{ $sortDirection }}',
    columnTypes: {{ json_encode($columnTypes) }},
    currencySymbol: '{{ $currencySymbol }}',

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
    },
    format(value, key) {
        const type = this.columnTypes[key] || 'string';
        
        switch (type) {
            case 'currency':
                return this.currencySymbol + parseFloat(value).toFixed(2);
            case 'number':
                return parseFloat(value).toLocaleString();
            case 'date':
                return new Date(value).toLocaleDateString();
            case 'boolean':
                return value ? '✓' : '✗';
            case 'string':
            default:
                // Default to string, no special formatting needed
                return value;
        }
    }
}" class="surface rounded-xl overflow-hidden">
    @if ($loading)
        <div class="p-6 space-y-4 animate-pulse">
            <div class="h-6 bg-secondary rounded w-1/4"></div>
            <div class="space-y-3">
                @foreach (range(1, 5) as $i)
                    <div class="h-4 bg-secondary rounded {{ $i % 2 ? 'w-5/6' : 'w-4/6' }}"></div>
                @endforeach
            </div>
        </div>
    @elseif(empty($data))
        <x-empty-state title="No data available" :description="$emptyMessage" icon="document" />
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary">
                <thead class="bg-tertiary">
                    <tr>
                        @foreach ($headers as $key => $header)
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary uppercase tracking-wider cursor-pointer select-none"
                                @click="sort('{{ $key }}')">
                                <div class="flex items-center space-x-1">
                                    <span>{{ $header }}</span>
                                    <template x-if="sortBy === '{{ $key }}'">
                                        <template x-if="sortDirection === 'asc'">
                                            <svg class="h-4 w-4 text-muted" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7" />
                                            </svg>
                                        </template>
                                        <template x-if="sortDirection === 'desc'">
                                            <svg class="h-4 w-4 text-muted" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7" />
                                            </svg>
                                        </template>
                                    </template>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-primary divide-y divide-secondary">
                    @foreach ($data as $row)
                        <tr class="hover:bg-secondary transition-colors">
                            @foreach ($headers as $key => $header)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                    <span x-text="format(@js($row[$key]), @js($key))"></span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>