<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($store) ? '✏️ ' . __('Edit Store') : '🏪 ' . __('Create New Store') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form
                        action="{{ isset($store) ? route('inventory.stores.update', $store) : route('inventory.stores.store') }}"
                        method="POST">
                        @csrf
                        @if (isset($store))
                            @method('PUT')
                        @endif
                        {{-- Show all errors --}}
                        @if ($errors->any())
                            <div
                                class="text-red-600 font-bold p-2 mb-2 bg-teal-400 bg-opacity-80 rounded-md shadow-inner shadow-lime-700 text-xl uppercase">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="space-y-8">
                            <!-- Store Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">🏪 Store Information</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <x-form.label for="name" value="Store Name *" />
                                        <x-form.input id="name" name="name" type="text"
                                            class="mt-1 block w-full" :value="old('name', $store->name ?? '')" required autofocus
                                            placeholder="e.g., Main Store, Warehouse, Workshop" />
                                        <x-form.input-error for="name" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="code" value="Store Code *" />
                                        <x-form.input id="code" name="code" type="text"
                                            class="mt-1 block w-full" :value="old('code', $store->code ?? '')" required
                                            placeholder="e.g., STORE-001, WH-01" />
                                        <x-form.input-error for="code" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="location" value="Location" />
                                        <x-form.input id="location" name="location" type="text"
                                            class="mt-1 block w-full" :value="old('location', $store->location ?? '')"
                                            placeholder="e.g., 123 Main Street, Building A, Floor 2" />
                                        <x-form.input-error for="location" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="description" value="Description" />
                                        <x-form.textarea id="description" name="description" class="mt-1 block w-full"
                                            rows="3"
                                            placeholder="Describe this store location...">{{ old('description', $store->description ?? '') }}</x-form.textarea>
                                        <x-form.input-error for="description" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Organization -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">🏢 Organization</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <x-form.label for="organization_unit_id" value="Organization Unit" />
                                        <x-form.select id="organization_unit_id" name="organization_unit_id"
                                            class="mt-1 block w-full">
                                            {{-- Your dynamic options go here in the slot --}}
                                            <option value="">Select Organization Unit</option>
                                            @foreach ($organizationUnits as $unit)
                                                <option value="{{ $unit->id }}"
                                                    {{ old('organization_unit_id', $store->organization_unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                    @if ($unit->parent)
                                                        ({{ $unit->parent->name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </x-form.select>
                                        <x-form.input-error for="organization_unit_id" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Settings -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">⚙️ Settings</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input type="hidden" name="is_active" value="0">
                                        <x-form.checkbox id="is_active" name="is_active" :checked="old('is_active', $store->is_active ?? true)" />
                                        <x-form.label for="is_active" value="Active Store" class="ml-2" />
                                    </div>
                                    <p class="text-sm text-gray-500 ml-7">
                                        Inactive stores won't appear in transaction dropdowns but will retain their
                                        data.
                                    </p>
                                </div>
                            </div>
                            <!-- Form Actions -->
                            <div class="flex justify-end space-x-3 mt-8 pt-8 border-t border-gray-200">
                                <x-button.secondary href="{{ route('inventory.stores.index') }}">
                                    Cancel
                                </x-button.secondary>
                                <x-button.primary type="submit">
                                    {{ isset($store) ? 'Update Store' : 'Create Store' }}
                                </x-button.primary>
                            </div>
                    </form>
                    <!-- Danger Zone (for edit only) -->
                    @if (isset($store))
                        <div class="border-t border-gray-200 pt-8">
                            <h3 class="text-lg font-medium text-red-900 mb-4">🗑️ Danger Zone</h3>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="text-sm font-medium text-red-800">Delete this store</h4>
                                        <p class="text-sm text-red-600 mt-1">
                                            Once deleted, this store and all its inventory data cannot be
                                            recovered.
                                            Make sure you have backups if needed.
                                        </p>
                                    </div>
                                    <x-button.danger type="button"
                                        onclick="confirm('Are you sure you want to delete this store? This action cannot be undone.') && document.getElementById('delete-store-form').submit()">
                                        Delete Store
                                    </x-button.danger>
                                </div>
                            </div>
                            <form id="delete-store-form" action="{{ route('inventory.stores.destroy', $store) }}"
                                method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                                asd
                            </form>
                        </div>
                    @endif
                </div>


            </div>
        </div>
    </div>
    </div>
</x-app-layout>
