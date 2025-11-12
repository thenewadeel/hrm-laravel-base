@props(['filters' => [], 'placeholder' => 'Search...'])

{{--
- Text search across all fields
- Filter by: Category, Store, Status, Price Range
- Save search presets
 --}}
<div class="bg-white p-4 rounded-lg border border-gray-200 mb-6">
    <div class="flex flex-col md:flex-row md:items-end space-y-4 md:space-y-0 md:space-x-4">
        <!-- Search Input -->
        <div class="flex-1">
            <x-form.label for="search" value="Search" />
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-s-magnifying-glass class="h-4 w-4 text-gray-400" />
                </div>
                <x-form.input id="search" name="search" type="text" class="pl-10 block w-full" :value="request('search')"
                    :placeholder="$placeholder" />
            </div>
        </div>

        <!-- Filters -->
        @foreach ($filters as $filterName => $filterOptions)
            <div>
                <x-form.label for="filter_{{ $filterName }}" value="{{ ucfirst($filterName) }}" />
                <x-form.select id="filter_{{ $filterName }}" name="filter_{{ $filterName }}"
                    class="mt-1 block w-full md:w-40">
                    <option value="">All {{ ucfirst($filterName) }}</option>
                    @foreach ($filterOptions as $key => $value)
                        <option value="{{ $key }}"
                            {{ request('filter_' . $filterName) == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
        @endforeach

        <!-- Action Buttons -->
        <div class="flex space-x-2">
            <x-button.primary type="submit">
                Apply Filters
            </x-button.primary>
            <x-button.secondary href="{{ url()->current() }}">
                Clear
            </x-button.secondary>
        </div>
    </div>
</div>
