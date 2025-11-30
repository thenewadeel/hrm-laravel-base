{{-- Example: Using ModelSelectDropdown in Salary Voucher Form --}}

{{-- OLD IMPLEMENTATION --}}
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

{{-- NEW IMPLEMENTATION --}}
@php
    $employeeOptions = $employees->map(function ($employee) {
        return [
            'value' => $employee->id,
            'label' => $employee->first_name . ' ' . $employee->last_name,
            'description' => $employee->employee_id ?? null,
            'searchTerms' => [
                $employee->first_name,
                $employee->last_name,
                $employee->employee_id ?? '',
                $employee->user->email ?? '',
            ]
        ];
    })->toArray();
@endphp

<x-model-select-dropdown
    name="employee_id"
    label="Employee"
    :value="$employee_id"
    :options="$employeeOptions"
    placeholder="Select an employee"
    searchable
    required
    search-placeholder="Search by name, ID, or email..."
/>

{{-- Livewire Component Update --}}
{{-- 
In the Livewire component, you need to transform the collection:

public function render()
{
    $employeeOptions = Employee::where('organization_id', auth()->user()->current_organization_id)
        ->where('is_active', true)
        ->with('user')
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->get()
        ->map(function ($employee) {
            return [
                'value' => $employee->id,
                'label' => $employee->first_name . ' ' . $employee->last_name,
                'description' => $employee->employee_id ?? null,
                'searchTerms' => [
                    $employee->first_name,
                    $employee->last_name,
                    $employee->employee_id ?? '',
                    $employee->user->email ?? '',
                ]
            ];
        })->toArray();

    return view('livewire.accounting.salary-voucher-form', [
        'employeeOptions' => $employeeOptions,
    ]);
}
--}}