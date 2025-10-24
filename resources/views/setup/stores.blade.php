{{-- resources/views/setup/stores.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Store Setup
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Progress Bar -->
                    <div class="mb-8">
                        <div class="flex justify-between mb-2">
                            <div class="text-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center bg-green-500 text-white">
                                    ✓
                                </div>
                                <span class="text-xs mt-1 block">Organization</span>
                            </div>
                            <div class="text-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center bg-blue-500 text-white">
                                    2
                                </div>
                                <span class="text-xs mt-1 block">Store</span>
                            </div>
                            <div class="text-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-300 text-gray-600">
                                    3
                                </div>
                                <span class="text-xs mt-1 block">Accounting</span>
                            </div>
                        </div>
                        <div class="bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 33%"></div>
                        </div>
                    </div>

                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">
                            Create Your First Store
                        </h1>
                        <p class="text-gray-600">
                            Set up your main store location to manage inventory
                        </p>
                    </div>

                    <form method="POST" action="{{ route('setup.stores.store') }}">
                        @csrf

                        <div class="space-y-4 max-w-md mx-auto">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Store Name *
                                </label>
                                <input type="text" name="name" id="name" required autofocus
                                    value="{{ old('name') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700">
                                    Location
                                </label>
                                <input type="text" name="location" id="location" value="{{ old('location') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('location')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700">
                                    Store Code
                                </label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}"
                                    placeholder="Auto-generated if left blank"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('code')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between mt-6">
                                <a href="{{ route('setup.organization') }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Back
                                </a>

                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Continue to Accounting
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
