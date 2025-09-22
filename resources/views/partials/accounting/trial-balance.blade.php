<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Trial Balance</h2>
    <p class="text-gray-600">This report shows the ending balance of each general ledger account.</p>
    <div class="shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($trialBalanceData as $row)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['account'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($row['debit'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($row['credit'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="bg-gray-100 font-bold">
                    <td class="px-6 py-4 whitespace-nowrap text-sm">Total</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        ${{ number_format(collect($trialBalanceData)->sum('debit'), 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        ${{ number_format(collect($trialBalanceData)->sum('credit'), 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
