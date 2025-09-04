<div class="p-4">
    <h3 class="text-xl font-semibold text-gray-900 mb-4">Trial Balance Report</h3>
    <p class="text-gray-600 mb-6">This report lists the ending balances in all of the company's general ledger accounts. Debits and credits must be equal.</p>
    <div class="overflow-x-auto shadow-md rounded-xl">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Account Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Debit
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Credit
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($data as $row)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $row['account_name'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($row['debit'] > 0)
                        ${{ number_format($row['debit'], 2) }}
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($row['credit'] > 0)
                        ${{ number_format($row['credit'], 2) }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        No trial balance data available.
                    </td>
                </tr>
                @endforelse
                <tr class="bg-gray-50 font-bold">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        Total
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${{ number_format($debitTotal, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${{ number_format($creditTotal, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>