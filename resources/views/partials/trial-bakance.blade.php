<div class="p-4">
    <h3 class="text-xl font-semibold text-gray-900 mb-4">Trial Balance Report</h3>
    <p class="text-gray-600">This report lists the ending balances in all of the company's general ledger accounts.
        Debits and credits must be equal.</p>{{-- Trial balance table will go here --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Account</th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Debit</th>
                    <th scope="col"
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Credit</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">@php$totalDebit = 0;$totalCredit = 0;@endphp
                @foreach ($data as $row)
                    @php$totalDebit += $row['debit'];$totalCredit += $row['credit'];@endphp<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ row['account'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                            {{ number_format(row['debit'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                            {{ number_format(row['credit'], 2) }}</td>
                    </tr>
                @endforeach\<tr
                    class="bg-gray-100 dark:bg-gray-700 font-bold text-gray-900 dark:text-gray-100">
                    <td class="px-6 py-4 whitespace-nowrap text-sm uppercase">Total</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ number_format(totalDebit, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
