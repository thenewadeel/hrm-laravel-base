{{-- resources/views/setup/accounts.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Accounting Setup
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Progress Bar -->
                    <div class="mb-8">
                        <div class="flex justify-between mb-2">
                            <div class="text-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center bg-green-500 text-white">
                                    ✓
                                </div>
                                <span class="text-xs mt-1 block">Organization</span>
                            </div>
                            <div class="text-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center bg-green-500 text-white">
                                    ✓
                                </div>
                                <span class="text-xs mt-1 block">Store</span>
                            </div>
                            <div class="text-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center bg-blue-500 text-white">
                                    3
                                </div>
                                <span class="text-xs mt-1 block">Accounting</span>
                            </div>
                        </div>
                        <div class="bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 66%"></div>
                        </div>
                    </div>

                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">
                            Set Up Your Accounting
                        </h1>
                        <p class="text-gray-600">
                            Configure your chart of accounts to track finances
                        </p>
                    </div>

                    <form method="POST" action="{{ route('setup.accounts.store') }}">
                        @csrf

                        <div class="space-y-6 max-w-2xl mx-auto">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <input type="checkbox" name="setup_default_accounts" id="setup_default_accounts"
                                            value="1" checked
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3">
                                        <label for="setup_default_accounts" class="font-medium text-gray-900">
                                            Set up default Chart of Accounts
                                        </label>
                                        <p class="mt-1 text-sm text-gray-600">
                                            This will create standard accounting accounts like Cash, Accounts
                                            Receivable,
                                            Sales Revenue, and common expense accounts. Recommended for most businesses.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                                <h3 class="font-medium text-gray-900 mb-3">Default accounts that will be created:</h3>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <h4 class="font-medium text-gray-700 mb-2">Assets</h4>
                                        <ul class="text-gray-600 space-y-1">
                                            <li>• Cash</li>
                                            <li>• Accounts Receivable</li>
                                            <li>• Inventory</li>
                                            <li>• Equipment</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-700 mb-2">Liabilities & Equity</h4>
                                        <ul class="text-gray-600 space-y-1">
                                            <li>• Accounts Payable</li>
                                            <li>• Loans Payable</li>
                                            <li>• Owner's Equity</li>
                                            <li>• Retained Earnings</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-6">
                                <a href="{{ route('setup.stores') }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Back
                                </a>

                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Complete Setup
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
