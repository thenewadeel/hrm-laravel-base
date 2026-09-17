<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            📦 {{ __('Inventory Setup') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-primary mb-4">{{ __('Setup Checklist') }}</h3>
                    <ol class="list-decimal list-inside space-y-4 text-secondary">
                        <li>
                            <span class="font-medium text-primary">{{ __('Organization Structure') }}</span>
                            <p class="text-sm text-muted mt-1">{{ __('Define your organization and its hierarchy.') }}</p>
                        </li>
                        <li>
                            <span class="font-medium text-primary">{{ __('Default Stores') }}</span>
                            <p class="text-sm text-muted mt-1">{{ __('Create the stores and warehouses you track inventory in.') }}</p>
                        </li>
                        <li>
                            <span class="font-medium text-primary">{{ __('Item Categories') }}</span>
                            <p class="text-sm text-muted mt-1">{{ __('Set up categories to organize your items.') }}</p>
                        </li>
                        <li>
                            <span class="font-medium text-primary">{{ __('Initial Stock Import') }}</span>
                            <p class="text-sm text-muted mt-1">{{ __('Import or record your opening stock balances.') }}</p>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>