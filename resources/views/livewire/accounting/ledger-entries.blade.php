<div class="space-y-4">
    <div class="px-0 py-2 sm:px-0">
        <h3 class="text-xl leading-6 font-semibold text-gray-900">{{ $title ?? 'Ledger Entries Detail' }}</h3>
        @if (isset($subTitle))
            <p class="mt-1 text-sm text-gray-500">
                {{ $subTitle ??
                    "This table displays the movements of funds (debits and credits) for the
                                                                    transaction." }}
            </p>
        @endif
    </div>{{--
    The <x-data-table> component is used here.
    It receives the headers and column types defined in the Livewire PHP class,
    and the processed data from the computed property $this->processedEntries.
--}}
    <x-data-table :headers="$headers" :data="$this->processedEntries" :columnTypes="$columnTypes"
        emptyMessage="No ledger entries recorded for this item." sortBy="entry_date" sortDirection="desc"
        currencySymbol="Rs. " />
</div>
