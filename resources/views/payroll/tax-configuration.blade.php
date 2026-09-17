<x-app-layout>
    <x-slot name="header">
        <x-page-header title="💰 {{ __('Payroll Tax Configuration') }}" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-page-header
                title="Tax Configuration"
                :description="'Manage income tax brackets for payroll calculations • '.auth()->user()->currentOrganization->name"
            >
                <x-slot name="actions">
                    <a href="{{ route('payroll.dashboard') }}">
                        <x-button variant="secondary">
                            <x-heroicon-o-arrow-left class="mr-2 size-4" />
                            {{ __('Back to Dashboard') }}
                        </x-button>
                    </a>
                </x-slot>
            </x-page-header>

            <x-card :title="__('Active Tax Brackets')">
                <x-slot name="actions">
                    <a href="{{ route('payroll.tax') }}">
                        <x-button variant="success" type="button">
                            <x-heroicon-o-plus class="mr-2 size-4" />
                            {{ __('Add Tax Bracket') }}
                        </x-button>
                    </a>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary">
                        <thead class="bg-tertiary">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Income Range') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Rate') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Base Tax') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Exemption') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted uppercase tracking-wider">{{ __('Effective Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary">
                            @forelse($taxBrackets as $bracket)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                        {{ $bracket->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        ${{ number_format($bracket->min_income, 2) }}
                                        -
                                        {{ $bracket->max_income === null ? '∞' : '$'.number_format($bracket->max_income, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        {{ $bracket->rate }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        ${{ number_format($bracket->base_tax, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                                        ${{ number_format($bracket->exemption_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary">
                                        {{ $bracket->effective_date?->format('M d, Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-muted">
                                        {{ __('No tax brackets configured yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <x-card :title="__('Add Tax Bracket')">
                <form action="{{ route('payroll.tax.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-secondary">{{ __('Bracket Name') }}</label>
                            <input type="text" name="name" id="name" required placeholder="e.g. Band A"
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="effective_date" class="block text-sm font-medium text-secondary">{{ __('Effective Date') }}</label>
                            <input type="date" name="effective_date" id="effective_date" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="min_income" class="block text-sm font-medium text-secondary">{{ __('Minimum Income ($)') }}</label>
                            <input type="number" name="min_income" id="min_income" step="0.01" min="0" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="max_income" class="block text-sm font-medium text-secondary">{{ __('Maximum Income ($)') }} <span class="text-xs text-muted">({{ __('optional') }})</span></label>
                            <input type="number" name="max_income" id="max_income" step="0.01" min="0"
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="rate" class="block text-sm font-medium text-secondary">{{ __('Tax Rate (%)') }}</label>
                            <input type="number" name="rate" id="rate" step="0.01" min="0" max="100" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label for="base_tax" class="block text-sm font-medium text-secondary">{{ __('Base Tax ($)') }}</label>
                            <input type="number" name="base_tax" id="base_tax" step="0.01" min="0" value="0" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="exemption_amount" class="block text-sm font-medium text-secondary">{{ __('Exemption Amount ($)') }}</label>
                            <input type="number" name="exemption_amount" id="exemption_amount" step="0.01" min="0" value="0" required
                                class="mt-1 w-full rounded-md border border-secondary surface px-3 py-2 text-sm text-primary focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-button variant="success" type="submit">
                            <x-heroicon-o-check class="mr-2 size-4" />
                            {{ __('Save Tax Bracket') }}
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>