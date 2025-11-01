<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($item) ? __('Edit Item') : __('Create New Item') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ isset($item) ? route('inventory.items.update', $item) : route('inventory.items.store') }}" method="POST">
                        @csrf
                        @if(isset($item))
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 gap-6">
                            <!-- Basic Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-form.label for="name" value="Item Name *" />
                                        <x-form.input id="name" name="name" type="text" class="mt-1 block w-full" 
                                            :value="old('name', $item->name ?? '')" required autofocus />
                                        <x-form.input-error for="name" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="sku" value="SKU *" />
                                        <x-form.input id="sku" name="sku" type="text" class="mt-1 block w-full" 
                                            :value="old('sku', $item->sku ?? '')" required />
                                        <x-form.input-error for="sku" class="mt-2" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <x-form.label for="description" value="Description" />
                                        <x-form.textarea id="description" name="description" class="mt-1 block w-full" 
                                            rows="3">{{ old('description', $item->description ?? '') }}</x-form.textarea>
                                        <x-form.input-error for="description" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Category & Organization -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Classification</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-form.label for="category" value="Category" />
                                        <x-form.select id="category" name="category" class="mt-1 block w-full">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category }}" 
                                                    {{ old('category', $item->category ?? '') == $category ? 'selected' : '' }}>
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        </x-form.select>
                                        <x-form.input-error for="category" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="unit" value="Unit of Measure *" />
                                        <x-form.input id="unit" name="unit" type="text" class="mt-1 block w-full" 
                                            :value="old('unit', $item->unit ?? 'pcs')" required />
                                        <x-form.input-error for="unit" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="head_id" value="Department/Head" />
                                        <x-form.select id="head_id" name="head_id" class="mt-1 block w-full">
                                            <option value="">Select Head</option>
                                            @foreach($heads as $head)
                                                <option value="{{ $head->id }}" 
                                                    {{ old('head_id', $item->head_id ?? '') == $head->id ? 'selected' : '' }}>
                                                    {{ $head->name }}
                                                </option>
                                            @endforeach
                                        </x-form.select>
                                        <x-form.input-error for="head_id" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-form.label for="cost_price" value="Cost Price (cents)" />
                                        <x-form.input id="cost_price" name="cost_price" type="number" step="1" 
                                            class="mt-1 block w-full" 
                                            :value="old('cost_price', $item->cost_price ?? '')" />
                                        <x-form.input-error for="cost_price" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="selling_price" value="Selling Price (cents)" />
                                        <x-form.input id="selling_price" name="selling_price" type="number" step="1" 
                                            class="mt-1 block w-full" 
                                            :value="old('selling_price', $item->selling_price ?? '')" />
                                        <x-form.input-error for="selling_price" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Inventory Settings -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Inventory Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-form.label for="reorder_level" value="Reorder Level" />
                                        <x-form.input id="reorder_level" name="reorder_level" type="number" 
                                            class="mt-1 block w-full" 
                                            :value="old('reorder_level', $item->reorder_level ?? 0)" />
                                        <x-form.input-error for="reorder_level" class="mt-2" />
                                    </div>

                                    <div class="flex items-center">
                                        <x-form.checkbox id="is_active" name="is_active" 
                                            :checked="old('is_active', $item->is_active ?? true)" />
                                        <x-form.label for="is_active" value="Active Item" class="ml-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                            <x-button.secondary href="{{ route('inventory.items.index') }}">
                                Cancel
                            </x-button.secondary>
                            <x-button.primary type="submit">
                                {{ isset($item) ? 'Update Item' : 'Create Item' }}
                            </x-button.primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>