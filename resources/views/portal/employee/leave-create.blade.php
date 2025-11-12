<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🏢 Apply for Leave
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Leave Application View
                </p>
            </div>
            <div class="flex space-x-2">
                <x-button.outline>
                    <x-heroicon-s-cog-6-tooth class="w-4 h-4 mr-2" />
                    Settings
                </x-button.outline>
                <x-button.primary>
                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                    Generate Report
                </x-button.primary>
            </div>
        </div>
    </x-slot>
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                        Apply for Leave
                    </h3>

                    <form action="{{ route('portal.employee.leave.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Leave Type -->
                            <div>
                                <label for="leave_type" class="block text-sm font-medium text-gray-700">Leave
                                    Type</label>
                                <select id="leave_type" name="leave_type" required
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">Select Leave Type</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="vacation">Vacation</option>
                                    <option value="personal">Personal Leave</option>
                                    <option value="emergency">Emergency Leave</option>
                                    <option value="maternity">Maternity Leave</option>
                                    <option value="paternity">Paternity Leave</option>
                                </select>
                                @error('leave_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Leave Balance Display -->
                            <div class="bg-blue-50 p-4 rounded-md">
                                <div class="text-sm font-medium text-blue-800">Available Balance</div>
                                <div class="text-2xl font-bold text-blue-900">{{ $leaveBalance }} days</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-6">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start
                                    Date</label>
                                <input type="date" id="start_date" name="start_date" required
                                    min="{{ today()->format('Y-m-d') }}"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" id="end_date" name="end_date" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Calculated Days -->
                        <div id="days-calculator" class="mt-4 p-3 bg-gray-50 rounded-md hidden">
                            <div class="text-sm text-gray-700">
                                <strong>Duration:</strong> <span id="calculated-days">0</span> days
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="mt-6">
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                            <textarea id="reason" name="reason" rows="4" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Please provide a reason for your leave..."></textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @error('leave_days')
                            <div class="mt-4 p-3 bg-red-50 rounded-md">
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            </div>
                        @enderror

                        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('portal.employee.leave') }}"
                                class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Calculate leave days when dates change
        document.getElementById('start_date').addEventListener('change', calculateLeaveDays);
        document.getElementById('end_date').addEventListener('change', calculateLeaveDays);

        function calculateLeaveDays() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

                const calculator = document.getElementById('days-calculator');
                const calculatedDays = document.getElementById('calculated-days');

                calculatedDays.textContent = diffDays;
                calculator.classList.remove('hidden');

                // Check if exceeds balance
                if (diffDays > {{ $leaveBalance }}) {
                    calculator.className = 'mt-4 p-3 bg-red-50 rounded-md';
                } else {
                    calculator.className = 'mt-4 p-3 bg-green-50 rounded-md';
                }
            }
        }
    </script>
</x-app-layout>
