<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-primary">Employee Increments</h2>
            <button wire:click="$toggle('showCreateForm')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>New Increment
            </button>
        </div>

        <!-- Search -->
        <div class="surface p-4 rounded-lg shadow">
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Search employees..." 
                   class="w-full px-4 py-2 border border-primary rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Create Form -->
        @if ($showCreateForm)
            <div class="surface p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4 text-primary">Create New Increment</h3>
                
                <form wire:submit="createIncrement" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-primary mb-1">Employee</label>
                            <select wire:model="employee_id" class="w-full px-3 py-2 border border-primary rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary mb-1">Increment Type</label>
                            <select wire:model="increment_type" class="w-full px-3 py-2 border border-primary rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="percentage">Percentage</option>
                                <option value="fixed_amount">Fixed Amount</option>
                            </select>
                            @error('increment_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary mb-1">
                                {{ $increment_type === 'percentage' ? 'Percentage (%)' : 'Amount ($)' }}
                            </label>
                            <input type="number" 
                                   wire:model="increment_value" 
                                   step="0.01" 
                                   min="0"
                                   class="w-full px-3 py-2 border border-primary rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('increment_value') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary mb-1">Effective Date</label>
                            <input type="date" 
                                   wire:model="effective_date" 
                                   class="w-full px-3 py-2 border border-primary rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('effective_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-primary mb-1">Reason</label>
                        <textarea wire:model="reason" 
                                  rows="3" 
                                  class="w-full px-3 py-2 border border-primary rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Reason for increment..."></textarea>
                        @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="$toggle('showCreateForm')" 
                                class="px-4 py-2 border border-primary text-primary rounded-lg hover:bg-primary transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Create Increment
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Increments Table -->
        <div class="surface rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-primary">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Salary Change</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Effective Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="surface divide-y border-secondary">
                        @forelse ($increments as $increment)
                            <tr class="hover:bg-primary">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-primary">
                                        {{ $increment->employee->first_name }} {{ $increment->employee->last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-primary capitalize">{{ $increment->increment_type }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-primary">
                                        {{ $increment->increment_type === 'percentage' ? $increment->increment_value . '%' : '$' . number_format($increment->increment_value, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-primary">
                                        ${{ number_format($increment->previous_salary, 2) }} → ${{ number_format($increment->new_salary, 2) }}
                                    </div>
                                    <div class="text-xs text-green-600">
                                        +{{ $increment->increment_percentage }}%
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                    {{ $increment->effective_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $increment->status === 'implemented' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $increment->status === 'approved' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $increment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $increment->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($increment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if ($increment->status === 'pending')
                                        <button wire:click="approveIncrement({{ $increment->id }})" 
                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                            Approve
                                        </button>
                                    @endif
                                    
                                    @if ($increment->status === 'approved')
                                        <button wire:click="implementIncrement({{ $increment->id }})" 
                                                class="text-green-600 hover:text-green-900 mr-3">
                                            Implement
                                        </button>
                                    @endif
                                    
                                    @if ($increment->status === 'pending')
                                        <button wire:click="deleteIncrement({{ $increment->id }})" 
                                                class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-muted">
                                    No increments found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            {{ $increments->links() }}
        </div>
    </div>
</div>
