<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    👤 Edit Employee: {{ $employee->first_name }} {{ $employee->last_name }}
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Update employee information and settings
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
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
                                <a href="{{ route('hr.employees.show', $employee) }}" class="ml-4 text-secondary hover:text-primary">
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-muted" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-4 text-primary font-medium">Edit</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Form -->
            <form action="{{ route('hr.employees.update', $employee) }}" method="POST">
                @csrf
                @method('PUT')
                
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
                                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-primary">
                                        Last Name
                                    </label>
                                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="middle_name" class="block text-sm font-medium text-primary">
                                        Middle Name
                                    </label>
                                    <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-primary">
                                        Email
                                    </label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $employee->email) }}" required
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-primary">
                                        Phone
                                    </label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-primary">
                                        Date of Birth
                                    </label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="gender" class="block text-sm font-medium text-primary">
                                        Gender
                                    </label>
                                    <select id="gender" name="gender" 
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $employee->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="organization_unit_id" class="block text-sm font-medium text-primary">
                                        Department
                                    </label>
                                    <select id="organization_unit_id" name="organization_unit_id"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Department</option>
                                        @foreach($organizationUnits as $unit)
                                            <option value="{{ $unit->id }}" {{ old('organization_unit_id', $employee->organization_unit_id) == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('address', $employee->address) }}</textarea>
                                </div>

                                <div>
                                    <label for="city" class="block text-sm font-medium text-primary">
                                        City
                                    </label>
                                    <input type="text" id="city" name="city" value="{{ old('city', $employee->city) }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="state" class="block text-sm font-medium text-primary">
                                        State
                                    </label>
                                    <input type="text" id="state" name="state" value="{{ old('state', $employee->state) }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="zip_code" class="block text-sm font-medium text-primary">
                                        ZIP Code
                                    </label>
                                    <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code', $employee->zip_code) }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="country" class="block text-sm font-medium text-primary">
                                        Country
                                    </label>
                                    <input type="text" id="country" name="country" value="{{ old('country', $employee->country) }}"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job & Payroll Information -->
                    <div class="surface shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-primary mb-4">Job & Payroll Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="position" class="block text-sm font-medium text-primary">
                                        Position
                                    </label>
                                    <input type="text" id="position" name="position" value="{{ old('position', $employee->organizationUser?->position) }}" required
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="salary_per_month" class="block text-sm font-medium text-primary">
                                        Monthly Salary
                                    </label>
                                    <input type="number" id="salary_per_month" name="salary_per_month" value="{{ old('salary_per_month', $employee->salary_per_month) }}" step="0.01" min="0"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="required_daily_hours" class="block text-sm font-medium text-primary">
                                        Required Daily Hours
                                    </label>
                                    <input type="number" id="required_daily_hours" name="required_daily_hours" value="{{ old('required_daily_hours', $employee->required_daily_hours) }}" step="0.1" min="0" max="24"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div class="flex items-center space-x-4 pt-6">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_admin" name="is_admin" value="1" {{ old('is_admin', $employee->is_admin) ? 'checked' : '' }}
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-secondary rounded">
                                        <label for="is_admin" class="ml-2 block text-sm text-primary">
                                            System Administrator
                                        </label>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-secondary rounded">
                                        <label for="is_active" class="ml-2 block text-sm text-primary">
                                            Active Employee
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('hr.employees.show', $employee) }}" 
                            class="bg-gray-300 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Employee
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
