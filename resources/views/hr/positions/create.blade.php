<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    ➕ {{ __('Create Job Position') }}
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Add a new job position to the organization
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="surface shadow-md rounded-lg">
                <form method="POST" action="{{ route('hr.positions.store') }}">
                    @csrf

                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-primary">Title</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}"
                                       class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       required>
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Code -->
                            <div>
                                <label for="code" class="block text-sm font-medium text-primary">Code</label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}"
                                       class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       required>
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Department -->
                            <div>
                                <label for="organization_unit_id" class="block text-sm font-medium text-primary">Department</label>
                                <select name="organization_unit_id" id="organization_unit_id"
                                        class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Department</option>
                                    @foreach($organizationUnits as $unit)
                                        <option value="{{ $unit->id }}" {{ old('organization_unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('organization_unit_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Salary Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="min_salary" class="block text-sm font-medium text-primary">Min Salary</label>
                                    <input type="number" name="min_salary" id="min_salary" value="{{ old('min_salary') }}" step="0.01"
                                           class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('min_salary')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="max_salary" class="block text-sm font-medium text-primary">Max Salary</label>
                                    <input type="number" name="max_salary" id="max_salary" value="{{ old('max_salary') }}" step="0.01"
                                           class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('max_salary')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-primary">Description</label>
                                <textarea name="description" id="description" rows="4"
                                          class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-secondary rounded">
                                <label for="is_active" class="ml-2 block text-sm text-primary">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-tertiary flex justify-end space-x-3">
                        <a href="{{ route('hr.positions.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-secondary rounded-md shadow-sm text-sm font-medium text-primary bg-surface hover:bg-tertiary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Position
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>