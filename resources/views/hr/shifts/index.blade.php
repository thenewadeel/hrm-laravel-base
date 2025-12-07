<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            🕒 {{ __('Shifts') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-primary sm:text-3xl sm:truncate">
                        Shifts
                    </h2>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <a href="{{ route('hr.shifts.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Add Shift
                    </a>
                </div>
            </div>

            <!-- Shifts List -->
            <div class="surface shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-secondary">
                    @foreach ($shifts as $shift)
                        <li>
                            <a href="{{ route('hr.shifts.show', $shift) }}" class="block hover:bg-tertiary">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                    <span class="text-green-600 font-medium">{{ substr($shift->name, 0, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-primary">
                                                    {{ $shift->name }}
                                                </div>
                                                <div class="text-sm text-secondary">{{ $shift->code }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <div class="text-sm text-primary">
                                                {{ $shift->start_time }} - {{ $shift->end_time }}
                                            </div>
                                            <div class="text-sm text-secondary">
                                                {{ $shift->working_hours }} hours
                                            </div>
                                            <div class="text-sm text-secondary">
                                                {{ implode(', ', array_map('ucfirst', $shift->days_of_week)) }}
                                            </div>
                                            <div>
                                                @if($shift->is_active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $shifts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>