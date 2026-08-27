<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    ➕ Add New Employee
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Create an employee record with system access
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <a href="{{ route('hr.employees.index') }}" class="text-secondary hover:text-primary">
                                Employees
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-muted" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-4 text-primary font-medium">Add</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Form -->
            <form action="{{ route('hr.employees.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <!-- Personal Information -->
                    <div class="surface shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-primary mb-4">Personal Information</h3>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-primary">
                                        First Name
                                    </label>
                                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('first_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-primary">
                                        Last Name
                                    </label>
                                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('last_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="middle_name" class="block text-sm font-medium text-primary">
                                        Middle Name
                                    </label>
                                    <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-primary">
                                        Email
                                    </label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-primary">
                                        Phone
                                    </label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-primary">
                                        Date of Birth
                                    </label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="gender" class="block text-sm font-medium text-primary">
                                        Gender
                                    </label>
                                    <select id="gender" name="gender"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="department" class="block text-sm font-medium text-primary">
                                        Department
                                    </label>
                                    <select id="department" name="organization_unit_id"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Department</option>
                                        @foreach($organizationUnits as $unit)
                                            <option value="{{ $unit->id }}" {{ old('organization_unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('organization_unit_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Access / Login -->
                    <div class="surface shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-primary mb-4">System Access & Roles</h3>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-primary">
                                        Password
                                    </label>
                                    <input type="password" id="password" name="password" required
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-primary">
                                        Confirm Password
                                    </label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" required
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="position_id" class="block text-sm font-medium text-primary">
                                        Position
                                    </label>
                                    <select id="position_id" name="position_id"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Position</option>
                                        @foreach($jobPositions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                                {{ $position->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-1">
                                        <a href="{{ route('hr.positions.create', ['return_to' => route('hr.employees.create')]) }}"
                                            class="inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-800">
                                            + Add new position
                                        </a>
                                    </div>
                                    @error('position_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="shift_id" class="block text-sm font-medium text-primary">
                                        Shift
                                    </label>
                                    <select id="shift_id" name="shift_id"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Shift</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                                {{ $shift->name }} ({{ \Illuminate\Support\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($shift->end_time)->format('H:i') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-1">
                                        <a href="{{ route('hr.shifts.create', ['return_to' => route('hr.employees.create')]) }}"
                                            class="inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-800">
                                            + Add new shift
                                        </a>
                                    </div>
                                    @error('shift_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-medium text-primary mb-2">
                                    Roles
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach(['inventory_admin', 'store_manager', 'inventory_clerk', 'auditor'] as $role)
                                        <label class="flex items-center">
                                            <input type="checkbox" name="roles[]" value="{{ $role }}"
                                                @if(in_array($role, old('roles', ['inventory_clerk']))) checked @endif
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-secondary rounded">
                                            <span class="ml-2 text-sm text-primary">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('roles')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-4 flex items-center space-x-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="is_admin" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-secondary rounded">
                                    <label for="is_admin" class="ml-2 block text-sm text-primary">
                                        System Administrator
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="surface shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-primary mb-4">Address Information</h3>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-primary">
                                        Street Address
                                    </label>
                                    <textarea id="address" name="address" rows="3"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('address') }}</textarea>
                                </div>

                                <div>
                                    <label for="city" class="block text-sm font-medium text-primary">
                                        City
                                    </label>
                                    <input type="text" id="city" name="city" value="{{ old('city') }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="state" class="block text-sm font-medium text-primary">
                                        State
                                    </label>
                                    <input type="text" id="state" name="state" value="{{ old('state') }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="zip_code" class="block text-sm font-medium text-primary">
                                        ZIP Code
                                    </label>
                                    <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code') }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="country" class="block text-sm font-medium text-primary">
                                        Country
                                    </label>
                                    <input type="text" id="country" name="country" value="{{ old('country') }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job & Payroll Information -->
                    <div class="surface shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-primary mb-4">Job & Payroll Information</h3>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                                <div>
                                    <label for="salary_per_month" class="block text-sm font-medium text-primary">
                                        Monthly Salary
                                    </label>
                                    <input type="number" id="salary_per_month" name="salary_per_month" value="{{ old('salary_per_month') }}" step="0.01" min="0"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="pay_frequency" class="block text-sm font-medium text-primary">
                                        Pay Frequency
                                    </label>
                                    <select id="pay_frequency" name="pay_frequency"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="monthly" {{ old('pay_frequency', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="biweekly" {{ old('pay_frequency') == 'biweekly' ? 'selected' : '' }}>Biweekly</option>
                                        <option value="weekly" {{ old('pay_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="required_daily_hours" class="block text-sm font-medium text-primary">
                                        Required Daily Hours
                                    </label>
                                    <input type="number" id="required_daily_hours" name="required_daily_hours" value="{{ old('required_daily_hours', 8) }}" step="0.1" min="0" max="24"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('hr.employees.index') }}"
                            class="bg-gray-300 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Employee
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
