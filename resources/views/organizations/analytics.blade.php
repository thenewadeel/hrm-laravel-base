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
                    <x-heroicon-o-document-duplicate class="size-8" />
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