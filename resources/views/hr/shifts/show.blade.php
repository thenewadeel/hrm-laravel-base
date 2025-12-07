<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    🕒 {{ __('Shift Management > Shift Details') }}
                </h2>
                <p class="text-sm text-secondary mt-1">
                    View and manage shift schedule and assignments
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-primary sm:text-3xl sm:truncate">
                        {{ $shift->name }}
                    </h2>
                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                        <div class="mt-2 flex items-center text-sm text-secondary">
                            @if($shift->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active Shift
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactive Shift
                                </span>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center text-sm text-secondary">
                            Shift Code: {{ $shift->code }}
                        </div>
                        @if ($shift->created_at)
                            <div class="mt-2 flex items-center text-sm text-secondary">
                                Created: {{ $shift->created_at->format('F j, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <a href="{{ route('hr.shifts.edit', $shift) }}"
                        class="inline-flex items-center px-4 py-2 border border-secondary rounded-md shadow-sm text-sm font-medium text-primary bg-surface hover:bg-tertiary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Shift
                    </a>
                    <a href="{{ route('hr.shifts.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Back to Shifts
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column - Shift Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="surface shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                            <h3 class="text-lg leading-6 font-medium text-primary">
                                Shift Information
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-secondary">Shift Name</dt>
                                    <dd class="mt-1 text-sm text-primary">{{ $shift->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary">Shift Code</dt>
                                    <dd class="mt-1 text-sm text-primary font-mono">{{ $shift->code }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary">Start Time</dt>
                                    <dd class="mt-1 text-sm text-primary">{{ $shift->start_time->format('h:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary">End Time</dt>
                                    <dd class="mt-1 text-sm text-primary">{{ $shift->end_time->format('h:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary">Working Hours</dt>
                                    <dd class="mt-1 text-sm text-primary">{{ $shift->working_hours }} hours</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary">Duration</dt>
                                    <dd class="mt-1 text-sm text-primary">{{ number_format($shift->duration, 2) }} hours</dd>
                                </div>
                                @if ($shift->description)
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-secondary">Description</dt>
                                        <dd class="mt-1 text-sm text-primary">{{ $shift->description }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Working Days -->
                    <div class="surface shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                            <h3 class="text-lg leading-6 font-medium text-primary">
                                Working Days
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    <div class="text-center">
                                        <div class="@if(in_array($day, $shift->days_of_week)) bg-green-100 text-green-800 @else bg-gray-100 text-gray-400 @endif rounded-lg px-3 py-2">
                                            <div class="text-xs font-medium">{{ ucfirst(substr($day, 0, 3)) }}</div>
                                            <div class="text-lg mt-1">
                                                @if(in_array($day, $shift->days_of_week))
                                                    ✓
                                                @else
                                                    —
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-sm text-secondary">
                                <span class="font-medium">{{ count($shift->days_of_week) }}</span> working days per week
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Employees -->
                    <div class="surface shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                            <h3 class="text-lg leading-6 font-medium text-primary">
                                Assigned Employees ({{ $shift->employees->count() }})
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            @if($shift->employees->count() > 0)
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach($shift->employees as $employee)
                                        <div class="border border-secondary rounded-lg p-4 hover:bg-tertiary">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <span class="text-blue-600 font-medium">{{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-primary">
                                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                                    </div>
                                                    <div class="text-sm text-secondary">
                                                        EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}
                                                    </div>
                                                    @if($employee->organizationUnit)
                                                        <div class="text-xs text-muted">{{ $employee->organizationUnit->name }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-primary">No employees assigned</h3>
                                    <p class="mt-1 text-sm text-secondary">No employees are currently assigned to this shift.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Stats & Actions -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <div class="surface shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                            <h3 class="text-lg leading-6 font-medium text-primary">
                                Quick Stats
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="space-y-4">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-secondary">Status</dt>
                                    <dd>
                                        @if($shift->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-secondary">Employees</dt>
                                    <dd class="text-sm text-primary">{{ $shift->employees->count() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-secondary">Working Days</dt>
                                    <dd class="text-sm text-primary">{{ count($shift->days_of_week) }}/week</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-secondary">Daily Hours</dt>
                                    <dd class="text-sm text-primary">{{ $shift->working_hours }} hrs</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-secondary">Weekly Hours</dt>
                                    <dd class="text-sm text-primary">{{ $shift->working_hours * count($shift->days_of_week) }} hrs</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Time Schedule -->
                    <div class="surface shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                            <h3 class="text-lg leading-6 font-medium text-primary">
                                Time Schedule
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold text-primary">
                                    {{ $shift->start_time->format('h:i A') }}
                                </div>
                                <div class="text-sm text-secondary mb-2">to</div>
                                <div class="text-2xl font-bold text-primary">
                                    {{ $shift->end_time->format('h:i A') }}
                                </div>
                                @if($shift->end_time < $shift->start_time)
                                    <div class="mt-2 text-xs text-yellow-600 bg-yellow-50 rounded px-2 py-1">
                                        Overnight Shift
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="surface shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-secondary">
                            <h3 class="text-lg leading-6 font-medium text-primary">
                                Actions
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6 space-y-3">
                            <a href="{{ route('hr.shifts.edit', $shift) }}"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-secondary rounded-md shadow-sm text-sm font-medium text-primary bg-surface hover:bg-tertiary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Shift
                            </a>
                            
                            <form action="{{ route('hr.shifts.destroy', $shift) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this shift?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete Shift
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>