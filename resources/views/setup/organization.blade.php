{{-- resources/views/setup/organization.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Organization Setup
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
                                    class="w-8 h-8 rounded-full flex items-center justify-center bg-blue-500 text-white">
                                    1
                                </div>
                                <span class="text-xs mt-1 block">Organization</span>
                            </div>
                            <div class="text-center">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-300 text-gray-600">
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
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">
                            Create Your Organization
                        </h1>
                        <p class="text-gray-600">
                            Set up your organization to begin using the system
                        </p>
                    </div>

                    <form method="POST" action="{{ route('setup.organization.store') }}">
                        @csrf

                        <div class="space-y-4 max-w-md mx-auto">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Organization Name *
                                </label>
                                <input type="text" name="name" id="name" required autofocus
                                    value="{{ old('name') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Continue to Store Setup
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
