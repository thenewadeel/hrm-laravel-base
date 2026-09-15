<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="'🏢 '.$organization->name.' Dashboard'"
            description="{{ __('Overview of your organization\'s performance and activities') }}"
        />
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card
                title="{{ __('Organization Management') }}"
                description="{{ __('Manage your organization structure, departments, and teams') }}"
            >
                <x-slot name="icon">
                    <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </x-slot>
            </x-card>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card label="{{ __('Total Employees') }}"
                    :value="$metrics['total_employees']" tone="info">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Departments') }}"
                    :value="$metrics['total_departments']" tone="success">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Attendance Rate') }}"
                    :value="$metrics['attendance_rate'].'%'" tone="warning">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Monthly Payroll') }}"
                    :value="'$'.number_format($metrics['monthly_payroll'])" tone="primary">
                    <x-slot name="icon">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>
            </div>

            @if (! empty($departmentStats))
                <x-card title="{{ __('Department Distribution') }}">
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($departmentStats as $dept)
                            <div class="overflow-hidden rounded-lg bg-tertiary">
                                <div class="p-5">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="flex size-8 items-center justify-center rounded-full bg-primary">
                                                <span
                                                    class="text-sm font-medium text-inverse">{{ substr($dept['name'], 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4 w-0 flex-1">
                                            <dl>
                                                <dt class="truncate text-sm font-medium text-muted">{{ $dept['name'] }}</dt>
                                                <dd class="text-lg font-medium text-primary">{{ $dept['count'] }} {{ __('employees') }}</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-bg-tertiary px-5 py-2">
                                    <div class="text-sm text-muted">{{ $dept['percentage'] }}% {{ __('of workforce') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    </div>
</x-app-layout>