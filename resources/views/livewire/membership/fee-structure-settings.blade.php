<div>
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Fee Structure Settings</h2>
        <p class="text-gray-600 dark:text-gray-400">Configure and manage fee rules and pricing</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-lg mr-4">
                    <x-heroicon-o-face-smile class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Active Rules</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getActiveRulesCount() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg mr-4">
                    <x-heroicon-o-face-smile class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Annual Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($this->getTotalAnnualRevenue(), 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg mr-4">
                    <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Special Rules</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ count($specialRules) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Rules Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Fee Rules -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Fee Rules</h3>
                <button wire:click="showAddNewRule" class="px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <x-heroicon-o-plus class="w-4 h-4 inline mr-1" />
                    Add Rule
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                @foreach($feeRules as $key => $rule)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 {{ $rule['active'] ? '' : 'opacity-60' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $rule['name'] }}</h4>
                                    @if($rule['editable'])
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Editable
                                        </span>
                                    @endif
                                    @if($rule['active'])
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Active
                                        </span>
                                    @else
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $rule['description'] }}</p>
                                <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $rule['billing_cycle'] }}</span>
                                    <span>•</span>
                                    <span>Due: {{ $rule['due_date'] }}</span>
                                    <span>•</span>
                                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">${{ number_format($rule['amount'], 0) }}</span>
                                </div>
                            </div>
                            
                            @if($rule['editable'])
                                <div class="flex space-x-2 ml-4">
                                    <button wire:click="editRule('{{ $key }}')" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button wire:click="toggleRuleStatus('{{ $key }}')" class="text-{{ $rule['active'] ? 'yellow' : 'green' }}-600 dark:text-{{ $rule['active'] ? 'yellow' : 'green' }}-400 hover:text-{{ $rule['active'] ? 'yellow' : 'green' }}-800 dark:hover:text-{{ $rule['active'] ? 'yellow' : 'green' }}-300">
                                        @if($rule['active'])
                                            <x-heroicon-o-x-mark class="w-4 h-4" />
                                        @else
                                            <x-heroicon-o-check-circle class="w-4 h-4" />
                                        @endif
                                    </button>
                                    <button wire:click="deleteRule('{{ $key }}')" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Special Rules -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Special Rules & Discounts</h3>
            </div>
            
            <div class="p-6 space-y-4">
                @foreach($specialRules as $key => $rule)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 {{ $rule['active'] ? '' : 'opacity-60' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $rule['name'] }}</h4>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $rule['type'] === 'penalty' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                        {{ $rule['percentage'] }}% {{ $rule['type'] === 'penalty' ? 'Penalty' : 'Discount' }}
                                    </span>
                                    @if($rule['active'])
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Active
                                        </span>
                                    @else
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $rule['description'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Condition: {{ $rule['condition'] }}</p>
                            </div>
                            
                            <div class="flex space-x-2 ml-4">
                                <button wire:click="editRule('{{ $key }}')" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>
                                <button wire:click="toggleRuleStatus('{{ $key }}')" class="text-{{ $rule['active'] ? 'yellow' : 'green' }}-600 dark:text-{{ $rule['active'] ? 'yellow' : 'green' }}-400 hover:text-{{ $rule['active'] ? 'yellow' : 'green' }}-800 dark:hover:text-{{ $rule['active'] ? 'yellow' : 'green' }}-300">
                                    @if($rule['active'])
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    @else
                                        <x-heroicon-o-check-circle class="w-4 h-4" />
                                    @endif
                                </button>
                                <button wire:click="deleteRule('{{ $key }}')" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Edit Rule Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeEditModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Edit Rule</h3>
                    
                    <form wire:submit="updateRule">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input wire:model="editingData.name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <textarea wire:model="editingData.description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"></textarea>
                            </div>

                            @if(isset($editingData['amount']))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                                    <input wire:model="editingData.amount" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                </div>
                            @endif

                            @if(isset($editingData['percentage']))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Percentage</label>
                                    <input wire:model="editingData.percentage" type="number" step="0.1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                </div>
                            @endif

                            @if(isset($editingData['billing_cycle']))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Cycle</label>
                                    <select wire:model="editingData.billing_cycle" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                </div>
                            @endif

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="editingData.active" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" wire:click="closeEditModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Update Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Add New Rule Modal -->
    @if($showAddRuleModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeAddRuleModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add New Fee Rule</h3>
                    
                    <form wire:submit="addNewRule">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input wire:model="newRule.name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <textarea wire:model="newRule.description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                                <input wire:model="newRule.amount" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Cycle</label>
                                <select wire:model="newRule.billing_cycle" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                                <input wire:model="newRule.due_date" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="newRule.active" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" wire:click="closeAddRuleModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Add Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Event Listeners -->
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('show-notification', function (event) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                    event.type === 'success' ? 'bg-green-500 text-white' : 
                    event.type === 'warning' ? 'bg-yellow-500 text-white' : 
                    event.type === 'error' ? 'bg-red-500 text-white' : 'bg-gray-500 text-white'
                }`;
                notification.textContent = event.message;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 3000);
            });
        });
    </script>
</div>