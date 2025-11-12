<!-- resources/views/hrm/dashboard.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            👥 HRM Dashboard - {{ $organization->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>HRM Dashboard - Under Construction</p>
                    <p>Data being passed to view:</p>
                    <ul>
                        <li>Organization: {{ $organization->name }}</li>
                        <li>Employee Summary: {{ json_encode($employeeSummary) }}</li>
                        <li>Performance KPIs: {{ json_encode($performanceKpis) }}</li>
                        <li>organizationUnit Stats: {{ count($organizationUnitStats) }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
