<div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Model Select Dropdown Demo</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Single Employee Selection -->
        <div>
            <x-model-select-dropdown
                name="selected_employee"
                label="Select Employee"
                :value="$selectedEmployee"
                :options="$employees"
                placeholder="Choose an employee..."
                searchable
                :required="false"
                search-placeholder="Search by name or ID..."
            />
        </div>

        <!-- Multiple Employee Selection -->
        <div>
            <x-model-select-dropdown
                name="selected_employees"
                label="Select Multiple Employees"
                :value="$selectedEmployees"
                :options="$employees"
                placeholder="Choose employees..."
                searchable
                multiple
                :required="false"
                search-placeholder="Search employees..."
                max-visible="5"
            />
        </div>

        <!-- Account Selection -->
        <div>
            <x-model-select-dropdown
                name="selected_account"
                label="Select Account"
                :value="$selectedAccount"
                :options="$accounts"
                placeholder="Choose an account..."
                searchable
                :required="false"
                search-placeholder="Search accounts..."
                size="sm"
            />
        </div>

        <!-- Department Selection -->
        <div>
            <x-model-select-dropdown
                name="selected_department"
                label="Select Department"
                :value="$selectedDepartment"
                :options="$departments"
                placeholder="Choose a department..."
                searchable
                :required="true"
                search-placeholder="Search departments..."
                size="lg"
            />
        </div>
    </div>

    <!-- Usage Examples -->
    <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Usage Examples</h3>
        
        <div class="space-y-4">
            <div>
                <h4 class="font-medium text-gray-700 dark:text-gray-300">Basic Usage:</h4>
                <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>&lt;x-model-select-dropdown
    name="employee_id"
    label="Employee"
    :options="$employees"
    placeholder="Select an employee"
/&gt;</code></pre>
            </div>

            <div>
                <h4 class="font-medium text-gray-700 dark:text-gray-300">With Search and Multiple Selection:</h4>
                <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>&lt;x-model-select-dropdown
    name="employees"
    label="Employees"
    :options="$employees"
    searchable
    multiple
    placeholder="Choose employees"
    search-placeholder="Search by name..."
/&gt;</code></pre>
            </div>

            <div>
                <h4 class="font-medium text-gray-700 dark:text-gray-300">Options Format:</h4>
                <pre class="bg-gray-100 dark:bg-gray-800 p-3 rounded text-sm overflow-x-auto"><code>$options = [
    [
        'value' => 'emp_001',
        'label' => 'John Doe',
        'description' => 'EMP001',
        'searchTerms' => ['John', 'Doe', 'EMP001']
    ]
];</code></pre>
            </div>
        </div>
    </div>

    <!-- Current Selections Display -->
    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-300 mb-2">Current Selections</h3>
        <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
            <p><strong>Single Employee:</strong> {{ $selectedEmployee ?: 'None selected' }}</p>
            <p><strong>Multiple Employees:</strong> {{ implode(', ', $selectedEmployees) ?: 'None selected' }}</p>
            <p><strong>Account:</strong> {{ $selectedAccount ?: 'None selected' }}</p>
            <p><strong>Department:</strong> {{ $selectedDepartment ?: 'None selected' }}</p>
        </div>
    </div>
</div>
