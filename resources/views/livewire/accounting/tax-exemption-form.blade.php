<div>
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $taxExemption->exists ? 'Edit Tax Exemption' : 'New Tax Exemption' }}
            </h2>
            <a href="{{ route('accounting.tax.tax-exemptions.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                &larr; Back to Exemptions
            </a>
        </div>

        <form wire:submit="save" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6">
            {{-- Entity --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Entity Type *</label>
                    <select wire:model.live="entityType"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select entity type...</option>
                        <option value="Customer">Customer</option>
                        <option value="Vendor">Vendor</option>
                        <option value="Employee">Employee</option>
                    </select>
                    @error('entityType') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Entity *</label>
                    <select wire:model.live="entityId"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select entity...</option>
                        @if($entityType && isset($entityOptions[$entityType]))
                            @foreach($entityOptions[$entityType] as $entity)
                                <option value="{{ $entity->id }}">{{ method_exists($entity, 'getFullNameAttribute') ? $entity->full_name : $entity->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('entityId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Certificate --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Certificate Number *</label>
                    <input type="text" wire:model.live="taxExemption.certificate_number"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                    @error('taxExemption.certificate_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Exemption Type *</label>
                    <select wire:model.live="taxExemption.exemption_type"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select type...</option>
                        @foreach($exemptionTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('taxExemption.exemption_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Rates & Dates --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Exemption Percentage *</label>
                    <input type="number" wire:model.live="taxExemption.exemption_percentage" min="0" max="100" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                    @error('taxExemption.exemption_percentage') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax Rate</label>
                    <select wire:model.live="taxExemption.tax_rate_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">None (all taxes)</option>
                        @foreach($taxRates as $rate)
                            <option value="{{ $rate->id }}">{{ $rate->name }} ({{ $rate->rate }}%)</option>
                        @endforeach
                    </select>
                    @error('taxExemption.tax_rate_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Active</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model.live="taxExemption.is_active" class="rounded border-gray-300 dark:border-gray-600" />
                            Enable this exemption
                        </label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Issue Date *</label>
                    <input type="date" wire:model.live="taxExemption.issue_date"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                    @error('taxExemption.issue_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry Date</label>
                    <input type="date" wire:model.live="taxExemption.expiry_date"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                    @error('taxExemption.expiry_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Issuing Authority</label>
                    <input type="text" wire:model.live="taxExemption.issuing_authority"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                    @error('taxExemption.issuing_authority') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                    <input type="text" wire:model.live="taxExemption.reason"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" />
                    @error('taxExemption.reason') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea wire:model.live="taxExemption.notes" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                @error('taxExemption.notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('accounting.tax.tax-exemptions.index') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">{{ $taxExemption->exists ? 'Update' : 'Create' }} Exemption</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-message', ([message, type]) => {
                const toast = document.createElement('div');
                const colors = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
                toast.className = `fixed top-4 right-4 z-50 px-4 py-3 text-white rounded-lg shadow-lg ${colors} transition-opacity`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
            });
        });
    </script>
</div>
