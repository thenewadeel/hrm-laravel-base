@php
    $componentId = 'model-select-' . uniqid();
    $sizes = [
        'sm' => 'text-sm',
        'md' => 'text-base',
        'lg' => 'text-lg',
    ];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="relative" x-data="modelSelectDropdown({{ json_encode($options) }}, {{ json_encode($value) }}, {{ $searchable ? 'true' : 'false' }}, {{ $multiple ? 'true' : 'false' }}, {{ $maxVisible }})">
    <!-- Label -->
    <label for="{{ $componentId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>

    <!-- Select Trigger -->
    <div class="relative">
        <button 
            type="button"
            id="{{ $componentId }}"
            @click="toggleDropdown()"
            @keydown.escape.prevent="closeDropdown()"
            class="w-full {{ $sizeClasses }} rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-left focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer transition-colors duration-200"
            :class="{ 'ring-2 ring-blue-500 border-blue-500': isOpen }"
            aria-haspopup="listbox"
            :aria-expanded="isOpen"
            aria-required="{{ $required ? 'true' : 'false' }}"
        >
            <div class="flex items-center justify-between">
                <span class="block truncate" x-text="getDisplayText() || '{{ $placeholder }}'"></span>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        <!-- Hidden Input -->
        <input 
            type="hidden" 
            name="{{ $name }}" 
            :value="multiple ? JSON.stringify(selectedValues) : selectedValue"
            {{ $required ? 'required' : '' }}
        >
    </div>

    <!-- Dropdown -->
    <div 
        x-show="isOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="closeDropdown()"
        class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 max-h-60 overflow-hidden"
        x-cloak
    >
        <!-- Search Input -->
        <div x-show="{{ $searchable ? 'true' : 'false' }}" class="p-3 border-b border-gray-200 dark:border-gray-600">
            <div class="relative">
                <input 
                    type="text" 
                    x-ref="searchInput"
                    x-model="searchQuery"
                    @input="filterOptions()"
                    placeholder="{{ $searchPlaceholder }}"
                    class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Options List -->
        <div class="max-h-48 overflow-y-auto">
            <!-- Loading State -->
            <div x-show="isLoading" class="p-4 text-center text-gray-500 dark:text-gray-400">
                <svg class="animate-spin h-5 w-5 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading...
            </div>

            <!-- No Results -->
            <div x-show="!isLoading && filteredOptions.length === 0" class="p-4 text-center text-gray-500 dark:text-gray-400">
                No options found
            </div>

            <!-- Options -->
            <ul 
                role="listbox" 
                class="py-1"
                x-show="!isLoading"
            >
                <template x-for="option in filteredOptions.slice(0, maxVisible)" :key="option.value">
                    <li 
                        @click="selectOption(option)"
                        @keydown.enter.prevent="selectOption(option)"
                        @keydown.space.prevent="selectOption(option)"
                        role="option"
                        class="px-3 py-2 cursor-pointer transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-gray-600"
                        :class="{
                            'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300': isSelected(option),
                            'text-gray-900 dark:text-white': !isSelected(option)
                        }"
                    >
                        <div class="flex items-center justify-between">
                            <span class="block truncate" x-text="option.label"></span>
                            <span x-show="option.description" class="text-xs text-gray-500 dark:text-gray-400 ml-2" x-text="option.description"></span>
                        </div>
                    </li>
                </template>
                
                <!-- Show More Indicator -->
                <li x-show="filteredOptions.length > maxVisible" class="px-3 py-2 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-600/20">
                    And <span x-text="filteredOptions.length - maxVisible"></span> more options...
                </li>
            </ul>
        </div>

        <!-- Multiple Selection Actions -->
        <div x-show="{{ $multiple ? 'true' : 'false' }}" class="p-3 border-t border-gray-200 dark:border-gray-600 flex justify-between">
            <button 
                @click="selectAll()"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
            >
                Select All
            </button>
            <button 
                @click="clearSelection()"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
            >
                Clear
            </button>
        </div>
    </div>
</div>

<script>
function modelSelectDropdown(options, initialValue, searchable, multiple, maxVisible) {
    return {
        options: options,
        filteredOptions: [...options],
        selectedValue: multiple ? [] : (initialValue || ''),
        selectedValues: multiple ? (Array.isArray(initialValue) ? initialValue : []) : [],
        searchQuery: '',
        isOpen: false,
        isLoading: false,
        searchable: searchable,
        multiple: multiple,
        maxVisible: maxVisible,

        init() {
            // Initialize selected values
            if (multiple && Array.isArray(initialValue)) {
                this.selectedValues = initialValue;
            } else if (!multiple && initialValue) {
                this.selectedValue = initialValue;
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target)) {
                    this.closeDropdown();
                }
            });

            // Focus search input when dropdown opens
            this.$watch('isOpen', (isOpen) => {
                if (isOpen && this.searchable) {
                    this.$nextTick(() => {
                        this.$refs.searchInput?.focus();
                    });
                }
            });
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.filterOptions();
            }
        },

        closeDropdown() {
            this.isOpen = false;
            this.searchQuery = '';
            this.filterOptions();
        },

        filterOptions() {
            if (!this.searchQuery) {
                this.filteredOptions = [...this.options];
                return;
            }

            const query = this.searchQuery.toLowerCase();
            this.filteredOptions = this.options.filter(option => 
                option.label.toLowerCase().includes(query) ||
                (option.description && option.description.toLowerCase().includes(query)) ||
                (option.searchTerms && option.searchTerms.some(term => term.toLowerCase().includes(query)))
            );
        },

        selectOption(option) {
            if (this.multiple) {
                const index = this.selectedValues.indexOf(option.value);
                if (index > -1) {
                    this.selectedValues.splice(index, 1);
                } else {
                    this.selectedValues.push(option.value);
                }
            } else {
                this.selectedValue = option.value;
                this.closeDropdown();
            }
        },

        isSelected(option) {
            if (this.multiple) {
                return this.selectedValues.includes(option.value);
            }
            return this.selectedValue === option.value;
        },

        getDisplayText() {
            if (this.multiple) {
                if (this.selectedValues.length === 0) return '';
                if (this.selectedValues.length === 1) {
                    const option = this.options.find(opt => opt.value === this.selectedValues[0]);
                    return option ? option.label : '';
                }
                return `${this.selectedValues.length} items selected`;
            }
            
            const option = this.options.find(opt => opt.value === this.selectedValue);
            return option ? option.label : '';
        },

        selectAll() {
            this.selectedValues = this.filteredOptions.map(option => option.value);
        },

        clearSelection() {
            if (this.multiple) {
                this.selectedValues = [];
            } else {
                this.selectedValue = '';
            }
        }
    };
}
</script>