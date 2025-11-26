    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Trial Balance
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        A list of all accounts and their balances.
                    </p>
                </div>
                <div class="flex space-x-2">
                    <x-button.outline href="{{ route('accounting.download.trial-balance') }}?as_of_date={{ now()->format('Y-m-d') }}">
                        <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" />
                        Download PDF
                    </x-button.outline>
                </div>
            </div>
        </div>
    <div class="border-t border-gray-200">
        <dl>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Total Debits</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    ${{ number_format($report['total_debits'] ?? 0, 2) }}
                </dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Total Credits</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    ${{ number_format($report['total_credits'] ?? 0, 2) }}
                </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Is Balanced?</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{-- <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $report['is_balanced'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $report['is_balanced'] ? 'Yes' : 'No' }}
                    </span> --}}
                </dd>
            </div>
        </dl>
    </div>
    <div class="overflow-x-auto">
        <x-data-table :headers="[
            'code' => 'Code',
            'name' => 'Account',
            'type' => 'Type',
            'debits' => 'Debits',
            'credits' => 'Credits',
            'balance' => 'Balance',
        ]" :data="$report['accounts']" />

    </div>
</div>
