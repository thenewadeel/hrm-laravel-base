<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Income Statement
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            For the period {{ $report['period']['start_date'] ?? 'N/A' }} to
            {{ $report['period']['end_date'] ?? 'N/A' }}.
        </p>
    </div>
    <div class="border-t border-gray-200">
        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Total Revenue</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                ${{ number_format($report['total_revenue'] ?? 0, 2) }}
            </dd>
        </div>
        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">Total Expenses</dt>
            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                ${{ number_format($report['total_expenses'] ?? 0, 2) }}
            </dd>
        </div>
        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-bold text-gray-900">Net Income</dt>
            <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">
                ${{ number_format($report['net_income'] ?? 0, 2) }}
            </dd>
        </div>
    </div>
</div>
