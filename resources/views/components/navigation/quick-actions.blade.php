{{-- Quick Actions --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
            Quick Actions
        </h3>
        
        <div class="mt-6 space-y-3">
            <!-- Create New Voucher -->
            <a href="{{ route('accounting.vouchers.sales.create') }}" 
               class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:bg-indigo-700 dark:focus:bg-indigo-600 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4m8 0l8 8m-8-8v8m0 0l-8-8m8 8H4m0 0v8" />
                </svg>
                Create Voucher
            </a>
            
            <!-- Add Inventory Item -->
            <a href="{{ route('inventory.items.create') }}" 
               class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 dark:bg-green-500 hover:bg-green-700 dark:hover:bg-green-600 focus:outline-none focus:bg-green-700 dark:focus:bg-green-600 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4m8 0l8 8m-8-8v8m0 0l-8-8m8 8H4m0 0v8" />
                </svg>
                Add Item
            </a>
            
            <!-- Process Payroll -->
            <a href="{{ route('payroll.process') }}" 
               class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 dark:bg-purple-500 hover:bg-purple-700 dark:hover:bg-purple-600 focus:outline-none focus:bg-purple-700 dark:focus:bg-purple-600 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h2a2 2 0 002-2v-6a2 2 0 00-2-2h-2m-4 0v8m-6-4h6m6 4h6a2 2 0 002-2v-6a2 2 0 00-2-2h-2m-4 0v8" />
                </svg>
                Process Payroll
            </a>
        </div>
    </div>
</div>