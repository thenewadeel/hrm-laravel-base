<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🏢 Manager Portal
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Overview of your performance and activities
                </p>
            </div>
            <div class="flex space-x-2">
                <x-button.outline>
                    <x-heroicon-s-cog-6-tooth class="w-4 h-4 mr-2" />
                    Settings
                </x-button.outline>
                <x-button.primary>
                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                    Generate Report
                </x-button.primary>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Manager Portal
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Team management and oversight dashboard</p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <button
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Team Report
                    </button>
                    <button
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Approve Requests
                    </button>
                </div>
            </div>

            <!-- Team Metrics -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Team Size -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Team Size</dt>
                                    <dd class="text-lg font-semibold text-gray-900">12</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Approvals</dt>
                                    <dd class="text-lg font-semibold text-gray-900">5</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Attendance -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Team Attendance</dt>
                                    <dd class="text-lg font-semibold text-gray-900">92%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- On Leave Today -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">On Leave Today</dt>
                                    <dd class="text-lg font-semibold text-gray-900">2</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Team Overview -->
                <div class="lg:col-span-2">
                    <!-- Team Attendance Today -->
                    <div class="bg-white shadow rounded-lg mb-6">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Team Attendance - Today
                            </h3>
                            <div class="space-y-3">
                                @foreach ($teamAttendance as $member)
                                    <div
                                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 bg-gray-300 rounded-full flex items-center justify-center">
                                                <span
                                                    class="text-gray-600 font-medium text-sm">{{ $member['initials'] }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $member['name'] }}
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $member['role'] }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $member['status_class'] }}">
                                                {{ $member['status'] }}
                                            </span>
                                            <span class="text-sm text-gray-500">{{ $member['time'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Management Actions
                            </h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {{-- <a href="{{ route('portal.manager.attendance-approval') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"> --}}
                                Approve Attendance
                                {{-- </a> --}}
                                {{-- <a href="{{ route('portal.manager.leave-approval') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> --}}
                                Leave Requests
                                {{-- </a> --}}
                                {{-- <a href="{{ route('portal.manager.team-report') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> --}}
                                Team Reports
                                {{-- </a> --}}
                                {{-- <a href="{{ route('portal.manager.performance') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> --}}
                                Performance Reviews
                                {{-- </a> --}}
                                <a href="{{ route('portal.manager.team-attendance') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Team Attendance
                                </a>
                                <a href="{{ route('portal.manager.reports') }}"
                                    class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Approvals & Notifications -->
                <div class="space-y-6">
                    <!-- Pending Approvals -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Pending Approvals
                            </h3>
                            <div class="space-y-4">
                                <div class="border-l-4 border-yellow-400 pl-4 py-2">
                                    <p class="text-sm font-medium text-gray-900">Leave Request</p>
                                    <p class="text-sm text-gray-500">Maria Johnson - Nov 20-22</p>
                                    <div class="mt-2 flex space-x-2">
                                        <button
                                            class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Approve</button>
                                        <button
                                            class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Reject</button>
                                    </div>
                                </div>
                                <div class="border-l-4 border-yellow-400 pl-4 py-2">
                                    <p class="text-sm font-medium text-gray-900">Time Regularization</p>
                                    <p class="text-sm text-gray-500">David Wilson - Nov 15</p>
                                    <div class="mt-2 flex space-x-2">
                                        <button
                                            class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Approve</button>
                                        <button
                                            class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Reject</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Team On Leave -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Team On Leave
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-900">Sarah Chen</span>
                                    <span class="text-sm text-gray-500">Sick Leave</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-900">Mike Rodriguez</span>
                                    <span class="text-sm text-gray-500">Vacation</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
