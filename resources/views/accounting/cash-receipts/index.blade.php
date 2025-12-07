<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    💰 Cash Receipts
                </h2>
                <p class="text-sm text-secondary mt-1">
                    A list of all cash receipts including their reference numbers, amounts, and dates.
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                @can(App\Permissions\AccountingPermissions::CREATE_CASH_RECEIPTS)
                    <a href="{{ route('accounting.cash-receipts.create') }}" 
                       class="inline-flex items-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                        New Cash Receipt
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-secondary">
                                <thead class="bg-tertiary">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-primary sm:pl-6">
                                            Reference
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-primary">
                                            Date
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-primary">
                                            Account
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-primary">
                                            Amount
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-primary">
                                            Description
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-secondary surface">
                                    <!-- TODO: Implement cash receipts listing component -->
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-sm text-muted">
                                            Cash receipts listing will be implemented in next phase.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>