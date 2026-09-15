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
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
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