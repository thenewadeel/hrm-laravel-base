<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏠 {{ __('Employee Management > Employee Profile (EMP-1001)') }}
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Employee Profile: EMP-1001
                    </h2>
                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            Active Employee
                        </div>
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            Joined: January 15, 2024
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Employee Details
                    </button>
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Reset Biometric ID
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Personal & Contact Information -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Personal & Contact Information
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">John Smith</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">john.smith@company.com</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">+1 (555) 123-4567</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                    <dd class="mt-1 text-sm text-gray-900">March 15, 1985</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        123 Main Street<br>
                                        Anytown, AN 12345
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Payroll Details -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Payroll Details
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Monthly Salary</dt>
                                    <dd class="mt-1 text-sm text-gray-900">$5,000.00</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Pay Frequency</dt>
                                    <dd class="mt-1 text-sm text-gray-900">Monthly</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Bank Account</dt>
                                    <dd class="mt-1 text-sm text-gray-900">**** 4567</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tax ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900">T-123456789</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- HR & Organization -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                HR & Organization
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Job Title</dt>
                                    <dd class="mt-1 text-sm text-gray-900">Senior Developer</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date of Joining</dt>
                                    <dd class="mt-1 text-sm text-gray-900">January 15, 2024</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Department</dt>
                                    <dd class="mt-1 text-sm text-gray-900">Engineering</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Manager</dt>
                                    <dd class="mt-1 text-sm text-gray-900">Sarah Johnson</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Employment Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">Full-time</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Attendance Setup -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Attendance Setup
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Critical settings linking clock-in device to
                                user</p>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Biometric ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">204</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Daily Hours</dt>
                                    <dd class="mt-1 text-sm text-gray-900">8.0 hours</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Shift Schedule</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Day Shift
                                            </span>
                                            <span class="text-gray-600">9:00 AM - 6:00 PM</span>
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
