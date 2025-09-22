<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Balance Sheet</h2>
    <p class="text-gray-600">This report provides a snapshot of your company's financial position at a specific point in
        time.</p>{{-- Assets --}}
    <div class="shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th colspan="2" class="px-6 py-3 text-left text-lg font-bold text-gray-800 tracking-wider">Assets
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($balanceSheetData['assets'] as $asset)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $asset['name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($asset['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Liabilities and Equity --}}
    <div class="shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th colspan="2" class="px-6 py-3 text-left text-lg font-bold text-gray-800 tracking-wider">
                        Liabilities & Equity</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {{-- Liabilities --}}
                @foreach ($balanceSheetData['liabilities'] as $liability)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $liability['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($liability['amount'], 2) }}</td>
                    </tr>
                @endforeach
                {{-- Equity --}}
                @foreach ($balanceSheetData['equity'] as $equity)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $equity['name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($equity['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="bg-gray-100 font-bold">
                    <td class="px-6 py-4 whitespace-nowrap text-sm">Total Liabilities & Equity</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        ${{ number_format($balanceSheetData['total_liabilities_equity'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
