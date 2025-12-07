<div>
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity z-40" wire:click="closeModal"></div>

        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative w-full max-w-lg transform rounded-xl surface text-left shadow-2xl transition-all">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between rounded-t-xl bg-primary px-6 py-4">
                        <h3 class="text-xl font-semibold text-primary">
                            {{ $isEditing ? 'Edit Organization' : 'Create Organization' }}
                        </h3>
                        <button type="button" class="text-muted hover:text-secondary" wire:click="closeModal">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Form Fields --}}
                    <div class="p-6 space-y-6">
                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-primary mb-1">Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="name" wire:model="name"
                                class="mt-1 block w-full rounded-md border-primary shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm placeholder-muted"
                                placeholder="Enter organization name">
                            @error('name')
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-primary mb-1">Description</label>
                            <textarea id="description" wire:model="description" rows="4"
                                class="mt-1 block w-full rounded-md border-primary shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm placeholder-muted"
                                placeholder="Enter organization description"></textarea>
                            @error('description')
                                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="pt-2">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="is_active"
                                    class="h-4 w-4 rounded border-primary text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-primary">Active Organization</span>
                            </label>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex justify-end space-x-3 rounded-b-xl bg-primary px-6 py-4">
                        <x-button.secondary wire:click="closeModal">
                            Cancel
                        </x-button.secondary>

                        <x-button.primary wire:click="save">
                            {{ $isEditing ? 'Update' : 'Save' }}
                        </x-button.primary>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
