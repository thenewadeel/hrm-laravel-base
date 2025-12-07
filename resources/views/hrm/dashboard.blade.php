 <!-- resources/views/hrm/dashboard.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    👥 HRM Dashboard - {{ $organization->name }}
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Overview of human resources management and analytics
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- HRM Management Header -->
            <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 surface border-b border-secondary">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-primary">Human Resources Management</h3>
                            <p class="mt-1 text-sm text-secondary">
                                Manage employees, attendance, payroll, and performance
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-primary font-medium">HRM Dashboard - Under Construction</p>
                    <p class="text-secondary mt-2">Data being passed to view:</p>
                    <ul class="mt-2 space-y-1 text-sm text-secondary">
                        <li><span class="font-medium">Organization:</span> {{ $organization->name }}</li>
                        <li><span class="font-medium">Employee Summary:</span> {{ json_encode($employeeSummary) }}</li>
                        <li><span class="font-medium">Performance KPIs:</span> {{ json_encode($performanceKpis) }}</li>
                        <li><span class="font-medium">Organization Unit Stats:</span> {{ count($organizationUnitStats) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
