<div>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Tax Filings</h2>
            <div class="flex gap-2">
                <button wire:click="generateQuarterlyFilings"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Generate Quarterly
                </button>
                <button wire:click="calculatePenalties"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                    Calculate Penalties
                </button>
                <button wire:click="$set('showCreateModal', true)"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                    New Filing
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" wire:model.live="search" placeholder="Search filings..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                    <select wire:model.live="filterStatus"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="filed">Filed</option>
                        <option value="accepted">Accepted</option>
                        <option value="paid">Paid</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterType"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Types</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annual">Annual</option>
                        <option value="special">Special</option>
                    </select>
                </div>
                <div>
                    <button wire:click="$refresh" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">Refresh</button>
                </div>
            </div>
        </div>

        {{-- Filings Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th wire:click="sortBy('filing_number')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                Filing # @if($sortBy === 'filing_number')<span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tax Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Period</th>
                            <th wire:click="sortBy('due_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                Due Date @if($sortBy === 'due_date')<span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($filings as $filing)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $filing->filing_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $filing->taxRate->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $filing->getFilingTypeDisplayName() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $filing->period_start->format('M d') }} - {{ $filing->period_end->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $filing->due_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900 dark:text-white">${{ number_format($filing->tax_due, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ match($filing->status) { 'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', 'accepted' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', 'filed' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200', 'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', } }}">
                                        {{ $filing->getStatusDisplayName() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($filing->status === 'draft' || $filing->status === 'filed')
                                            <button wire:click="approveFiling({{ $filing->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400">Approve</button>
                                        @endif
                                        @if($filing->status === 'accepted')
                                            <button wire:click="markAsPaid({{ $filing->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Mark Paid</button>
                                        @endif
                                        @if(!in_array($filing->status, ['accepted', 'paid']))
                                            <button wire:click="delete({{ $filing->id }})" wire:confirm="Are you sure?" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No tax filings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($filings->hasPages())
                <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $filings->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Create Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity" wire:click="$set('showCreateModal', false)"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create Tax Filing</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax Rate</label>
                            <select wire:model.live="selectedTaxRateId"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                                <option value="">Select a tax rate...</option>
                                @foreach($taxRates as $rate)
                                    <option value="{{ $rate->id }}">{{ $rate->name }} ({{ $rate->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filing Type</label>
                            <select wire:model.live="filingType"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annual">Annual</option>
                                <option value="special">Special</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period Start</label>
                                <input type="date" wire:model.live="periodStart"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period End</label>
                                <input type="date" wire:model.live="periodEnd"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Cancel</button>
                            <button wire:click="createFiling" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Create Filing</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-message', ([message, type]) => {
                const toast = document.createElement('div');
                const colors = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
                toast.className = `fixed top-4 right-4 z-50 px-4 py-3 text-white rounded-lg shadow-lg ${colors} transition-opacity`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
            });
        });
    </script>
</div>
