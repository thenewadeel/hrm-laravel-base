<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏠 {{ __('Employee Management') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header with Stats -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Employee Management
                    </h2>
                    <div class="mt-2 flex items-center space-x-4">
                        <div class="flex items-center text-sm text-gray-500">
                            <span class="font-medium text-gray-900">{{ $employees->total() }}</span>
                            <span class="ml-1">total employees</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $employees->where('is_active', true)->count() }} Active
                            </span>
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $employees->where('is_active', false)->count() }} Inactive
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <a href="{{ route('hr.employees.create') }}" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Employee
                    </a>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white shadow rounded-lg mb-6 p-4">
                <form method="GET" action="{{ route('hr.employees.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name or email..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select id="department" 
                                name="department" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'department']))
                            <a href="{{ route('hr.employees.index') }}" 
                               class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Employee List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                @if($employees->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach ($employees as $employee)
                            <!-- Employee Row - Clickable -->
                            <li>
                                <a href="{{ route('hr.employees.show', $employee) }}" 
                                   class="block hover:bg-gray-50 transition-colors duration-150">
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center flex-1">
                                                <!-- Avatar -->
                                                <div class="flex-shrink-0">
                                                    @if($employee->user && $employee->user->avatar)
                                                        <img class="h-12 w-12 rounded-full object-cover" 
                                                             src="{{ $employee->user->avatar }}" 
                                                             alt="{{ $employee->first_name }} {{ $employee->last_name }}">
                                                    @else
                                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                                            <span class="text-white font-medium text-lg">
                                                                {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Employee Info -->
                                                <div class="ml-4 flex-1">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                                        </div>
                                                        <!-- Status Badge -->
                                                        @if($employee->is_active)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Active
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                Inactive
                                                            </span>
                                                        @endif
                                                        <!-- System Access Badge -->
                                                        @if($employee->hasLoginAccess())
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                                </svg>
                                                                Has Access
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd" />
                                                                </svg>
                                                                No Access
                                                            </span>
                                                        @endif
                                                        <!-- Admin Badge -->
                                                        @if($employee->is_admin)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                                                </svg>
                                                                Admin
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                                        <span>{{ $employee->email }}</span>
                                                        @if($employee->phone)
                                                            <span>• {{ $employee->phone }}</span>
                                                        @endif
                                                        @if($employee->organizationUnit)
                                                            <span>• {{ $employee->organizationUnit->name }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                                        <span>Employee ID: EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</span>
                                                        @if($employee->organizationUser && $employee->organizationUser->position)
                                                            <span>• {{ $employee->organizationUser->position }}</span>
                                                        @endif
                                                        @if($employee->biometric_id)
                                                            <span>• Biometric: {{ $employee->biometric_id }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Right Side Info & Arrow -->
                                            <div class="flex items-center space-x-4">
                                                <!-- Quick Stats -->
                                                <div class="text-right">
                                                    <div class="text-sm text-gray-900">
                                                        @if($employee->salary_per_month)
                                                            ${{ number_format($employee->salary_per_month, 0) }}/mo
                                                        @else
                                                            Salary not set
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $employee->created_at ? $employee->created_at->diffInYears(now()) : 0 }} years
                                                    </div>
                                                </div>
                                                <!-- Arrow -->
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No employees found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if(request()->hasAny(['search', 'department']))
                                Try adjusting your search criteria or 
                                <a href="{{ route('hr.employees.index') }}" class="text-blue-600 hover:text-blue-500">clear all filters</a>.
                            @else
                                Get started by adding your first employee.
                            @endif
                        </p>
                        @if(!request()->hasAny(['search', 'department']))
                            <div class="mt-6">
                                <a href="{{ route('hr.employees.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Employee
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($employees->hasPages())
                <div class="mt-6">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
