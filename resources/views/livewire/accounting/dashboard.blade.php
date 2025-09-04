<div class="p-6 bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">Financial Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Summary Cards -->
            @foreach ($summary as $key => $value)
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
                    <div class="text-sm font-semibold text-gray-500 dark:text-gray-400 capitalize">
                        {{ str_replace('_', ' ', $key) }}</div>
                    <div class="text-2xl font-bold mt-1 text-gray-900 dark:text-gray-100">${{ number_format($value, 2) }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Recent Transactions/Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Recent Activity</h2>
            <!-- This section would be a Livewire partial or component for a recent transactions feed -->
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                <li class="py-4 flex justify-between items-center">
                    <span class="text-gray-800 dark:text-gray-200">Payment for consulting services</span>
                    <span class="text-green-500 font-semibold">+ $5,000.00</span>
                </li>
                <li class="py-4 flex justify-between items-center">
                    <span class="text-gray-800 dark:text-gray-200">Office supplies expense</span>
                    <span class="text-red-500 font-semibold">- $250.00</span>
                </li>
                <li class="py-4 flex justify-between items-center">
                    <span class="text-gray-800 dark:text-gray-200">Rent payment</span>
                    <span class="text-red-500 font-semibold">- $1,200.00</span>
                </li>
            </ul>
        </div>
    </div>
</div>
