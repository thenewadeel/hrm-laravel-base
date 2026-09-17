<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            🏷️ {{ __('Item Categories') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-primary">{{ __('Category Tree') }}</h3>
                    <p class="text-sm text-muted">{{ __('Organize inventory items into a hierarchical category structure.') }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-primary">{{ __('Add and Edit Categories') }}</h3>
                    <p class="text-sm text-muted">{{ __('Create new categories and manage existing ones.') }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-primary">{{ __('Bulk Assign') }}</h3>
                    <p class="text-sm text-muted">{{ __('Assign items to categories in bulk.') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>