<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            ➕ {{ __('Add Department') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="surface shadow-md rounded-lg">
                <form method="POST" action="{{ route('organization.units.store') }}">
                    @csrf

                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-primary">
                                    Department Name
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                    class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-primary">
                                    Type
                                </label>
                                <select id="type" name="type" required
                                    class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="department" {{ old('type', 'department') == 'department' ? 'selected' : '' }}>Department</option>
                                    <option value="branch" {{ old('type') == 'branch' ? 'selected' : '' }}>Branch</option>
                                    <option value="division" {{ old('type') == 'division' ? 'selected' : '' }}>Division</option>
                                    <option value="head_office" {{ old('type') == 'head_office' ? 'selected' : '' }}>Head Office</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="parent_id" class="block text-sm font-medium text-primary">
                                    Parent Department
                                </label>
                                <select id="parent_id" name="parent_id"
                                    class="mt-1 block w-full border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">None (Top Level)</option>
                                    @foreach($parentUnits as $unit)
                                        <option value="{{ $unit->id }}" {{ old('parent_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-tertiary flex justify-end space-x-3 rounded-b-lg">
                        <a href="{{ route('organization.units.index') }}"
                            class="bg-gray-300 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-400">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">
                            Create Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
