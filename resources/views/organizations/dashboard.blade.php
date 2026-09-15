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
                    <x-heroicon-o-document-text class="size-8" />
                </x-slot>
            </x-card>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card label="{{ __('Total Employees') }}"
                    :value="$metrics['total_employees']" tone="info">
                    <x-slot name="icon">
                        <x-heroicon-o-user-group class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Departments') }}"
                    :value="$metrics['total_departments']" tone="success">
                    <x-slot name="icon">
                        <x-heroicon-o-document-text class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Attendance Rate') }}"
                    :value="$metrics['attendance_rate'].'%'" tone="warning">
                    <x-slot name="icon">
                        <x-heroicon-o-document-duplicate class="size-6" />
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card label="{{ __('Monthly Payroll') }}"
                    :value="'$'.number_format($metrics['monthly_payroll'])" tone="primary">
                    <x-slot name="icon">
                        <x-heroicon-o-face-smile class="size-6" />
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