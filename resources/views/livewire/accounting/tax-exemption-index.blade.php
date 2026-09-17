<div>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Tax Exemptions</h2>
            <a href="{{ route('accounting.tax.tax-exemptions.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                New Exemption
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" wire:model.live="search" placeholder="Search exemptions..."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                    <select wire:model.live="filterType"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Types</option>
                        @foreach($exemptionTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterStatus"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div>
                    <button wire:click="$refresh" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">Refresh</button>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th wire:click="sortBy('certificate_number')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                                Certificate # @if($sortBy === 'certificate_number')<span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Entity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Percentage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($exemptions as $exemption)
                            @php
                                $isExpired = $exemption->expiry_date && $exemption->expiry_date->isPast();
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $exemption->certificate_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    @if($exemption->exemptible)
                                        {{ class_basename($exemption->exemptible_type) }}
                                        @if(method_exists($exemption->exemptible, 'name'))
                                            &mdash; {{ $exemption->exemptible->name }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ ucfirst($exemption->exemption_type) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $exemption->exemption_percentage }}%</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $exemption->issue_date->format('M d, Y') }}
                                    @if($exemption->expiry_date)
                                        &mdash; {{ $exemption->expiry_date->format('M d, Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($isExpired)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">Expired</span>
                                    @elseif($exemption->is_active)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Active</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('accounting.tax.tax-exemptions.edit', $exemption) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Edit</a>
                                        <button wire:click="toggleStatus({{ $exemption->id }})" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400">
                                            {{ $exemption->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        <button wire:click="delete({{ $exemption->id }})" wire:confirm="Are you sure?" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No tax exemptions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($exemptions->hasPages())
                <div class="bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $exemptions->links() }}
                </div>
            @endif
        </div>
    </div>

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
