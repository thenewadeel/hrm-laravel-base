<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🎨 Inventory Components Demo
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Navigation -->
            <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex space-x-4 overflow-x-auto">
                    <a href="#stock-cards" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-medium whitespace-nowrap">📊 Stock Cards</a>
                    <a href="#alerts" class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-medium whitespace-nowrap">⚠️ Alerts</a>
                    <a href="#indicators" class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium whitespace-nowrap">📈 Indicators</a>
                    <a href="#buttons" class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-medium whitespace-nowrap">🎯 Buttons</a>
                    <a href="#tables" class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg text-sm font-medium whitespace-nowrap">📋 Tables</a>
                    <a href="#forms" class="px-4 py-2 bg-pink-100 text-pink-700 rounded-lg text-sm font-medium whitespace-nowrap">📝 Forms</a>
                </div>
            </div>

            <!-- Stock Cards Section -->
            <section id="stock-cards" class="mb-12">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">📊 Stock Cards</h3>
                    <p class="text-gray-600 mb-6">Quick overview cards for inventory metrics with trends and icons.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <x-inventory.stock-card 
                            title="Total Items"
                            value="156"
                            trend="+12%"
                            trendColor="bg-green-100 text-green-800"
                            description="Across all stores"
                            icon="📦"
                        />
                        
                        <x-inventory.stock-card 
                            title="Low Stock Items"
                            value="12"
                            trend="+5%"
                            trendColor="bg-yellow-100 text-yellow-800"
                            description="Need attention"
                            icon="⚠️"
                        />
                        
                        <x-inventory.stock-card 
                            title="Out of Stock"
                            value="3"
                            trend="-2%"
                            trendColor="bg-red-100 text-red-800"
                            description="Requires restocking"
                            icon="❌"
                        />
                        
                        <x-inventory.stock-card 
                            title="Total Value"
                            value="$15,240"
                            trend="+8%"
                            trendColor="bg-blue-100 text-blue-800"
                            description="Inventory worth"
                            icon="💰"
                        />
                    </div>
                </div>
            </section>

            <!-- Alert Components Section -->
            <section id="alerts" class="mb-12">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">⚠️ Alert Components</h3>
                    <p class="text-gray-600 mb-6">Different alert levels for inventory notifications.</p>
                    
                    <div class="space-y-4">
                        <x-inventory.low-stock-alert level="warning" :items="12">
                            12 items are below reorder level and need attention. 
                            <a href="#" class="font-medium underline">View low stock report</a>
                        </x-inventory.low-stock-alert>

                        <x-inventory.low-stock-alert level="danger" :items="3">
                            3 items are out of stock and require immediate restocking.
                            <a href="#" class="font-medium underline">View out of stock items</a>
                        </x-inventory.low-stock-alert>

                        <x-inventory.low-stock-alert level="info" :items="5">
                            Stock count completed for Main Store. 5 items need adjustment.
                            <a href="#" class="font-medium underline">Review adjustments</a>
                        </x-inventory.low-stock-alert>
                    </div>
                </div>
            </section>

            <!-- Status Indicators Section -->
            <section id="indicators" class="mb-12">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">📈 Status Indicators</h3>
                    <p class="text-gray-600 mb-6">Visual indicators for stock levels and statuses.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Quantity Indicators -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Quantity Indicators</h4>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">In Stock (Good)</span>
                                    <x-inventory.quantity-indicator 
                                        quantity="45" 
                                        reorderLevel="20" 
                                    />
                                </div>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Low Stock (Warning)</span>
                                    <x-inventory.quantity-indicator 
                                        quantity="12" 
                                        reorderLevel="20" 
                                    />
                                </div>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Out of Stock (Critical)</span>
                                    <x-inventory.quantity-indicator 
                                        quantity="0" 
                                        reorderLevel="20" 
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Status Badges -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Status Badges</h4>
                            <div class="space-y-4">
                                <div class="flex flex-wrap gap-4 p-4 bg-gray-50 rounded-lg">
                                    <x-status-badge status="active" />
                                    <x-status-badge status="inactive" />
                                    <x-status-badge status="draft" />
                                    <x-status-badge status="finalized" />
                                    <x-status-badge status="cancelled" />
                                    <x-status-badge status="posted" />
                                    <x-status-badge status="void" />
                                </div>
                                
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Usage in Tables:</h5>
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span>Steel Bolts</span>
                                            <x-status-badge status="active" />
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span>LED Bulbs</span>
                                            <x-status-badge status="inactive" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Button Components Section -->
            <section id="buttons" class="mb-12">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">🎯 Button Components</h3>
                    <p class="text-gray-600 mb-6">Various button styles for different actions.</p>
                    
                    <div class="space-y-6">
                        <!-- Primary Buttons -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Primary Actions</h4>
                            <div class="flex flex-wrap gap-3">
                                <x-button.primary>Add New Item</x-button.primary>
                                <x-button.primary>
                                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                                    Create Transaction
                                </x-button.primary>
                                <x-button.primary size="sm">Small Button</x-button.primary>
                                <x-button.primary disabled>Disabled</x-button.primary>
                            </div>
                        </div>

                        <!-- Secondary Buttons -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Secondary Actions</h4>
                            <div class="flex flex-wrap gap-3">
                                <x-button.secondary>Edit Item</x-button.secondary>
                                <x-button.secondary>
                                    <x-heroicon-s-pencil class="w-4 h-4 mr-2" />
                                    Update Stock
                                </x-button.secondary>
                                <x-button.secondary size="sm">Small</x-button.secondary>
                            </div>
                        </div>

                        <!-- Danger Buttons -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Danger Actions</h4>
                            <div class="flex flex-wrap gap-3">
                                <x-button.danger>Delete Item</x-button.danger>
                                <x-button.danger>
                                    <x-heroicon-s-trash class="w-4 h-4 mr-2" />
                                    Cancel Transaction
                                </x-button.danger>
                                <x-button.danger size="sm">Remove</x-button.danger>
                            </div>
                        </div>

                        <!-- Outline & Link Buttons -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Other Styles</h4>
                            <div class="flex flex-wrap gap-3">
                                <x-button.outline>View Details</x-button.outline>
                                <x-button.ghost>Ghost Button</x-button.ghost>
                                <x-button.link>Text Link</x-button.link>
                                <x-button.link href="#">Link with Href</x-button.link>
                            </div>
                        </div>

                        <!-- Quick Action Grid -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Quick Action Grid</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <x-button.primary class="flex flex-col items-center justify-center py-4">
                                    <span class="text-2xl mb-2">📦</span>
                                    <span class="text-sm">Add Item</span>
                                </x-button.primary>
                                
                                <x-button.secondary class="flex flex-col items-center justify-center py-4">
                                    <span class="text-2xl mb-2">📥</span>
                                    <span class="text-sm">Receive Stock</span>
                                </x-button.secondary>
                                
                                <x-button.secondary class="flex flex-col items-center justify-center py-4">
                                    <span class="text-2xl mb-2">📤</span>
                                    <span class="text-sm">Issue Items</span>
                                </x-button.secondary>
                                
                                <x-button.outline class="flex flex-col items-center justify-center py-4">
                                    <span class="text-2xl mb-2">📊</span>
                                    <span class="text-sm">View Reports</span>
                                </x-button.outline>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Table Components Section -->
            <section id="tables" class="mb-12">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">📋 Table Components</h3>
                    <p class="text-gray-600 mb-6">Data tables for displaying inventory items and transactions.</p>
                    
                    <!-- Items Table -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Items Table</h4>
                        <div class="overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                            <div class="p-4 bg-white">
                                <x-data-table>
                                    <x-slot name="header">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </x-slot>
                                    
                                    <x-slot name="body">
                                        <!-- Sample Row 1 -->
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                        <x-heroicon-s-cube class="h-6 w-6 text-gray-400" />
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">Steel Bolts</div>
                                                        <div class="text-sm text-gray-500">M6x20mm galvanized</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">BLT-001</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Hardware</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-inventory.quantity-indicator quantity="45" reorderLevel="20" />
                                                <div class="text-xs text-gray-500 mt-1">45 pcs</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$2.50</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-status-badge status="active" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <x-button.link href="#" size="sm">View</x-button.link>
                                                    <x-button.link href="#" size="sm">Edit</x-button.link>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Sample Row 2 -->
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                        <x-heroicon-s-cube class="h-6 w-6 text-gray-400" />
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">LED Bulbs</div>
                                                        <div class="text-sm text-gray-500">10W warm white</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">BUL-002</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Electrical</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-inventory.quantity-indicator quantity="12" reorderLevel="20" />
                                                <div class="text-xs text-gray-500 mt-1">12 pcs</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$8.99</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-status-badge status="active" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <x-button.link href="#" size="sm">View</x-button.link>
                                                    <x-button.link href="#" size="sm">Edit</x-button.link>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Sample Row 3 -->
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                        <x-heroicon-s-cube class="h-6 w-6 text-gray-400" />
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">Motor Oil</div>
                                                        <div class="text-sm text-gray-500">5W-30 synthetic</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">OIL-003</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Automotive</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-inventory.quantity-indicator quantity="0" reorderLevel="5" />
                                                <div class="text-xs text-gray-500 mt-1">0 pcs</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$15.99</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-status-badge status="inactive" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <x-button.link href="#" size="sm">View</x-button.link>
                                                    <x-button.link href="#" size="sm">Edit</x-button.link>
                                                </div>
                                            </td>
                                        </tr>
                                    </x-slot>
                                </x-data-table>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Transactions Table</h4>
                        <div class="overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                            <div class="p-4 bg-white">
                                <x-data-table>
                                    <x-slot name="header">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </x-slot>
                                    
                                    <x-slot name="body">
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-mono">REC-2024-001</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="text-lg mr-2">📥</span>
                                                    <span class="text-sm text-gray-900">Receipt</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Main Store</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">5 items</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-status-badge status="finalized" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 hours ago</td>
                                        </tr>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-mono">ISS-2024-002</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="text-lg mr-2">📤</span>
                                                    <span class="text-sm text-gray-900">Issue</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Workshop</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3 items</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <x-status-badge status="draft" />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4 hours ago</td>
                                        </tr>
                                    </x-slot>
                                </x-data-table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Form Components Section -->
            <section id="forms" class="mb-12">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">📝 Form Components</h3>
                    <p class="text-gray-600 mb-6">Form elements for creating and editing inventory data.</p>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Item Form -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Item Form</h4>
                            <div class="space-y-4 p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <x-form.label for="demo_name" value="Item Name *" />
                                    <x-form.input id="demo_name" name="demo_name" type="text" class="mt-1 block w-full" value="Steel Bolts" />
                                </div>
                                
                                <div>
                                    <x-form.label for="demo_sku" value="SKU *" />
                                    <x-form.input id="demo_sku" name="demo_sku" type="text" class="mt-1 block w-full" value="BLT-001" />
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-form.label for="demo_category" value="Category" />
                                        <x-form.select id="demo_category" name="demo_category" class="mt-1 block w-full">
                                            <option value="Hardware" selected>Hardware</option>
                                            <option value="Electrical">Electrical</option>
                                            <option value="Tools">Tools</option>
                                        </x-form.select>
                                    </div>
                                    
                                    <div>
                                        <x-form.label for="demo_unit" value="Unit" />
                                        <x-form.input id="demo_unit" name="demo_unit" type="text" class="mt-1 block w-full" value="pcs" />
                                    </div>
                                </div>
                                
                                <div>
                                    <x-form.label for="demo_description" value="Description" />
                                    <x-form.textarea id="demo_description" name="demo_description" class="mt-1 block w-full" rows="3">
M6x20mm galvanized steel bolts for general construction use.
                                    </x-form.textarea>
                                </div>
                                
                                <div class="flex items-center">
                                    <x-form.checkbox id="demo_active" name="demo_active" checked />
                                    <x-form.label for="demo_active" value="Active Item" class="ml-2" />
                                </div>
                                
                                <div class="flex justify-end space-x-3 pt-4">
                                    <x-button.secondary>Cancel</x-button.secondary>
                                    <x-button.primary>Save Item</x-button.primary>
                                </div>
                            </div>
                        </div>

                        <!-- Search & Filters -->
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Search & Filters</h4>
                            
                            <!-- Advanced Search -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                                <h5 class="font-medium text-gray-900 mb-3">Advanced Search</h5>
                                <x-advanced-search 
                                    :filters="[
                                        'category' => ['Hardware' => 'Hardware', 'Electrical' => 'Electrical', 'Tools' => 'Tools'],
                                        'status' => ['active' => 'Active', 'inactive' => 'Inactive'],
                                        'store' => ['Main Store' => 'Main Store', 'Workshop' => 'Workshop']
                                    ]"
                                    placeholder="Search items by name, SKU, or description..."
                                />
                            </div>

                            <!-- Date Range -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h5 class="font-medium text-gray-900 mb-3">Date Range Filter</h5>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <x-form.label for="demo_start_date" value="Start Date" />
                                        <x-form.input id="demo_start_date" name="demo_start_date" type="date" class="mt-1 block w-full" />
                                    </div>
                                    <div>
                                        <x-form.label for="demo_end_date" value="End Date" />
                                        <x-form.input id="demo_end_date" name="demo_end_date" type="date" class="mt-1 block w-full" />
                                    </div>
                                </div>
                                <div class="mt-3 flex space-x-2">
                                    <x-button.primary class="w-full">Apply</x-button.primary>
                                    <x-button.secondary>Reset</x-button.secondary>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Component Usage Notes -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
                <h3 class="text-lg font-semibold text-blue-800 mb-3">💡 Component Usage Notes</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-700">
                    <div>
                        <h4 class="font-medium mb-2">Stock Cards</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Use for dashboard overviews</li>
                            <li>Include trends and icons</li>
                            <li>Responsive grid layout</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Alerts & Indicators</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Different severity levels</li>
                            <li>Auto-calculated stock status</li>
                            <li>Consistent color coding</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Buttons & Actions</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Primary for main actions</li>
                            <li>Secondary for alternatives</li>
                            <li>Danger for destructive actions</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Tables & Forms</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Consistent data presentation</li>
                            <li>Mobile-responsive tables</li>
                            <li>Accessible form controls</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        html {
            scroll-behavior: smooth;
        }
        section {
            scroll-margin-top: 2rem;
        }
    </style>
</x-app-layout>