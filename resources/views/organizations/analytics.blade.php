<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="📊 {{ __('Analytics') }}"
            description="{{ __('Organization analytics and performance metrics') }}"
        />
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card
                title="{{ __('Analytics') }}"
                description="{{ __('Track your organization\'s performance and trends') }}"
            >
                <x-slot name="icon">
                    <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </x-slot>
            </x-card>

            <x-card title="{{ __('Headcount Trend') }}">
                <div class="bg-bg-tertiary mt-5 rounded-lg p-4">
                    <div class="grid grid-cols-6 gap-4">
                        @foreach ($analytics['headcount_trend']['labels'] as $index => $label)
                            <div class="text-center">
                                <div class="text-sm text-muted">{{ $label }}</div>
                                <div class="text-lg font-semibold text-primary">{{ $analytics['headcount_trend']['data'][$index] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-card>

            <x-card title="{{ __('Performance Metrics') }}">
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="bg-bg-tertiary rounded-lg p-4">
                        <div class="text-sm font-medium text-muted">{{ __('Attendance Rate') }}</div>
                        <div class="mt-1 text-2xl font-semibold text-primary">95.2%</div>
                        <div class="mt-1 text-sm text-success">↑ 2.1% {{ __('from last month') }}</div>
                    </div>
                    <div class="bg-bg-tertiary rounded-lg p-4">
                        <div class="text-sm font-medium text-muted">{{ __('Productivity') }}</div>
                        <div class="mt-1 text-2xl font-semibold text-primary">87.5%</div>
                        <div class="mt-1 text-sm text-success">↑ 1.3% {{ __('from last month') }}</div>
                    </div>
                    <div class="bg-bg-tertiary rounded-lg p-4">
                        <div class="text-sm font-medium text-muted">{{ __('Employee Satisfaction') }}</div>
                        <div class="mt-1 text-2xl font-semibold text-primary">4.2/5</div>
                        <div class="mt-1 text-sm text-warning">→ {{ __('Same as last month') }}</div>
                    </div>
                </div>
            </x-card>

            <x-card title="{{ __('Department Performance') }}">
                <div class="mt-5 space-y-4">
                    @foreach ($analytics['department_performance']['labels'] as $index => $department)
                        <div class="bg-bg-tertiary flex items-center justify-between rounded-lg p-4">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-primary">{{ $department }}</div>
                                <div class="mt-1">
                                    <div class="flex items-center">
                                        <span class="text-sm text-muted">{{ __('Productivity') }}:</span>
                                        <span class="ml-2 text-sm font-medium text-primary">{{ $analytics['department_performance']['productivity'][$index] }}%</span>
                                    </div>
                                    <div class="mt-1 flex items-center">
                                        <span class="text-sm text-muted">{{ __('Efficiency') }}:</span>
                                        <span class="ml-2 text-sm font-medium text-primary">{{ $analytics['department_performance']['efficiency'][$index] }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div
                                    class="flex size-16 items-center justify-center rounded-full border-4 border-primary/60">
                                    <span
                                        class="text-sm font-medium text-primary">{{ round(($analytics['department_performance']['productivity'][$index] + $analytics['department_performance']['efficiency'][$index]) / 2) }}%</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>