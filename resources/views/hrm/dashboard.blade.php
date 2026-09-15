{{-- resources/views/hrm/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="'👥 HRM Dashboard - '.$organization->name"
            description="{{ __('Overview of human resources management and analytics.') }}"
        />
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card
                title="{{ __('Human Resources Management') }}"
                description="{{ __('Manage employees, attendance, payroll, and performance.') }}"
            >
                <x-slot name="icon">
                    <x-heroicon-o-user-group class="size-6" />
                </x-slot>

                <p class="font-medium text-primary">{{ __('HRM Dashboard - Under Construction') }}</p>
                <p class="mt-2 text-secondary">{{ __('Data being passed to view:') }}</p>
                <ul class="mt-2 space-y-1 text-sm text-secondary">
                    <li><span class="font-medium">{{ __('Organization') }}:</span> {{ $organization->name }}</li>
                    <li><span class="font-medium">{{ __('Employee Summary') }}:</span> {{ json_encode($employeeSummary) }}
                    </li>
                    <li><span class="font-medium">{{ __('Performance KPIs') }}:</span> {{ json_encode($performanceKpis) }}
                    </li>
                    <li><span class="font-medium">{{ __('Organization Unit Stats') }}:</span> {{ count($organizationUnitStats) }}
                    </li>
                </ul>
            </x-card>
        </div>
    </div>
</x-app-layout>