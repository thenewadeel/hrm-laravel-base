<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($store) ? __('Edit Store') : __('Create New Store') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ isset($store) ? route('inventory.stores.update', $store) : route('inventory.stores.store') }}" method="POST">
                        @csrf
                        @if(isset($store))
                            @method('PUT')
                        @endif

                        <div class="space-y-6">
                            <!-- Store Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Store Information</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <x-form.label for="name" value="Store Name *" />
                                        <x-form.input id="name" name="name" type="text" class="mt-1 block w-full" 
                                            :value="old('name', $store->name ?? '')" required autofocus />
                                        <x-form.input-error for="name" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="code" value="Store Code *" />
                                        <x-form.input id="code" name="code" type="text" class="mt-1 block w-full" 
                                            :value="old('code', $store->code ?? '')" required />
                                        <x-form.input-error for="code" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="location" value="Location" />
                                        <x-form.input id="location" name="location" type="text" class="mt-1 block w-full" 
                                            :value="old('location', $store->location ?? '')" />
                                        <x-form.input-error for="location" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="description" value="Description" />
                                        <x-form.textarea id="description" name="description" class="mt-1 block w-full" 
                                            rows="3">{{ old('description', $store->description ?? '') }}</x-form.textarea>
                                        <x-form.input-error for="description" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Organization -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Organization</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <x-form.label for="organization_unit_id" value="Organization Unit" />
                                        <x-form.select id="organization_unit_id" name="organization_unit_id" class="mt-1 block w-full">
                                            <option value="">Select Organization Unit</option>
                                            @foreach($organizationUnits as $unit)
                                                <option value="{{ $unit->id }}" 
                                                    {{ old('organization_unit_id', $store->organization_unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </x-form.select>
                                        <x-form.input-error for="organization_unit_id" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Settings -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Settings</h3>
                                <div class="flex items-center">
                                    <x-form.checkbox id="is_active" name="is_active" 
                                        :checked="old('is_active', $store->is_active ?? true)" />
                                    <x-form.label for="is_active" value="Active Store" class="ml-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                            <x-button.secondary href="{{ route('inventory.stores.index') }}">
                                Cancel
                            </x-button.secondary>
                            <x-button.primary type="submit">
                                {{ isset($store) ? 'Update Store' : 'Create Store' }}
                            </x-button.primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>