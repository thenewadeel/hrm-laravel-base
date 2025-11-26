<div>
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Advance Reports</h1>
            <div class="flex gap-3">
                <button wire:click="resetFilters" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Reset Filters
                </button>
                <a href="/hrm/advances" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6m9-4h.01"></path>
                    </svg>
                    Manage Advances
                </a>
            </div>
        </div>

        <!-- Report Type Selector -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Report Type</label>
                <select wire:model.live="reportType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @foreach($this->reportTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if($reportType === 'employee-statement')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Employee</label>
                    <select wire:model.live="employeeId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">All Employees</option>
                        @if(isset($employees))
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                <input type="date" wire:model.live="startDate" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                <input type="date" wire:model.live="endDate" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <!-- Export Button -->
        <div class="flex justify-end">
            <button wire:click="exportPdf" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export PDF
            </button>
        </div>
    </div>

    <!-- Report Content -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        @switch($reportType)
            @case('overview')
                @include('livewire.payroll.reports.analytics-overview', ['reportData' => $this->reportData])
                @break

            @case('employee-statement')
                @include('livewire.payroll.reports.employee-statement', ['reportData' => $this->reportData])
                @break

            @case('aging-analysis')
                @include('livewire.payroll.reports.aging-analysis', ['reportData' => $this->reportData])
                @break

            @case('monthly-summary')
                @include('livewire.payroll.reports.monthly-summary', ['reportData' => $this->reportData])
                @break

            @case('department-report')
                @include('livewire.payroll.reports.department-report', ['reportData' => $this->reportData])
                @break

            @case('advance-vs-salary')
                @include('livewire.payroll.reports.advance-vs-salary', ['reportData' => $this->reportData])
                @break

            @case('outstanding')
                @include('livewire.payroll.reports.outstanding-advances', ['reportData' => $this->reportData])
                @break

            @default
                @include('livewire.payroll.reports.analytics-overview')
        @endswitch
    </div>
</div>
