    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Balance Sheet
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        As of {{ $report['as_of_date'] ?? 'N/A' }}.
                    </p>
                </div>
                <div class="flex space-x-2">
                    <x-button.outline href="{{ route('accounting.download.balance-sheet') }}?as_of_date={{ $report['as_of_date'] ?? now()->format('Y-m-d') }}">
                        <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                        Download PDF
                    </x-button.outline>
                </div>
            </div>
        </div>
    <div class="border-t border-gray-200">
        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Total Assets</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                ${{ number_format($report['total_assets'] ?? 0, 2) }}
            </dd>
        </div>
        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Total Liabilities</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                ${{ number_format($report['total_liabilities'] ?? 0, 2) }}
            </dd>
        </div>
        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Total Equity</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                ${{ number_format($report['total_equity'] ?? 0, 2) }}
            </dd>
        </div>
        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Is Balanced?</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $report['is_balanced'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $report['is_balanced'] ? 'Yes' : 'No' }}
                </span>
            </dd>
        </div>
    </div>

    <div class="overflow-x-auto">
        <h4 class="text-md font-semibold text-gray-900 px-6 py-4">Assets</h4>
        <x-data-table :headers="[
            'code' => 'Code',
            'name' => 'Name',
            'balance' => 'Balance',
        ]" :data="$report['assets']" :columnTypes="[
            'balance' => 'currency',
        ]" />

        {{-- <x-data-table :headers="[
            'code' => 'Code',
            'name' => 'Name',
            'balance' => 'Balance',
        ]" :data="$report['assets']" /> --}}
        {{-- <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($report['assets'] as $asset)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $asset['code'] }} -
                            {{ $asset['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($asset['balance'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table> --}}

        <h4 class="text-md font-semibold text-gray-900 px-6 py-4 mt-4">Liabilities</h4>
        <x-data-table :headers="[
            'code' => 'Code',
            'name' => 'Name',
            'balance' => 'Balance',
        ]" :data="$report['liabilities']" :columnTypes="[
            'balance' => 'currency',
        ]" />
        {{-- <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($report['liabilities'] as $liability)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $liability['code'] }} - {{ $liability['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($liability['balance'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table> --}}

        <h4 class="text-md font-semibold text-gray-900 px-6 py-4 mt-4">Equity</h4>
        <x-data-table :headers="[
            'code' => 'Code',
            'name' => 'Name',
            'balance' => 'Balance',
        ]" :data="$report['equity']" :columnTypes="[
            'balance' => 'currency',
        ]" />
        {{-- <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($report['equity'] as $equity)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $equity['code'] }}
                            - {{ $equity['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($equity['balance'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="bg-gray-100">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Retained Earnings</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                        ${{ number_format($report['retained_earnings'], 2) }}</td>
                </tr>
            </tbody>
        </table> --}}
    </div>
</div>
