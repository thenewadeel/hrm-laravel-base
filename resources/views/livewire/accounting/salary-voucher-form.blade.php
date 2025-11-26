<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Create Salary Voucher</h3>
    
    <form wire:submit="createSalaryVoucher">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Entry Date</label>
                <input type="date" wire:model.live="entry_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('entry_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                <select wire:model.live="employee_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                    @endforeach
                </select>
                @error('employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payroll Period</label>
                <input type="text" wire:model.live="payroll_period" placeholder="e.g., November 2025" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('payroll_period') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input type="text" wire:model.live="description" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        
        <!-- Salary Details -->
        <div class="mb-4">
            <h4 class="text-md font-medium text-gray-700 mb-2">Salary Details</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gross Salary</label>
                    <input type="number" wire:model.live="salary_amount" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('salary_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax Deduction</label>
                    <input type="number" wire:model.live="tax_deduction" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('tax_deduction') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Other Deductions</label>
                    <input type="number" wire:model.live="other_deductions" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('other_deductions') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        
        <!-- Net Salary Summary -->
        <div class="mb-4 p-4 bg-gray-50 rounded">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Deductions</label>
                    <div class="text-lg font-semibold">{{ number_format($this->tax_deduction + $this->other_deductions, 2) }}</div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Net Salary</label>
                    <div class="text-lg font-bold text-green-600">{{ number_format($this->calculateNetSalary(), 2) }}</div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Create Salary Voucher
            </button>
        </div>
    </form>
</div>
