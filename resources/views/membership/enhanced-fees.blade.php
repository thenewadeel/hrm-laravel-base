<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    💰 Enhanced Fee Management
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Comprehensive fee and dues management with advanced payment processing
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('fees.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                    <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Basic
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                    <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Report
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Revenue</p>
                            <p class="text-3xl font-bold mt-2">$45,678.90</p>
                            <p class="text-blue-100 text-xs mt-2">↑ 12% from last month</p>
                        </div>
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="h-8 w-8 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Collection Rate</p>
                            <p class="text-3xl font-bold mt-2">94.2%</p>
                            <p class="text-green-100 text-xs mt-2">↑ 3% from last month</p>
                        </div>
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="h-8 w-8 text-green-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Outstanding</p>
                            <p class="text-3xl font-bold mt-2">$8,234.50</p>
                            <p class="text-orange-100 text-xs mt-2">23 pending payments</p>
                        </div>
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="h-8 w-8 text-orange-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-100 text-sm font-medium">Overdue</p>
                            <p class="text-3xl font-bold mt-2">$2,145.00</p>
                            <p class="text-red-100 text-xs mt-2">8 overdue payments</p>
                        </div>
                        <div class="bg-white/20 rounded-full p-3">
                            <svg class="h-8 w-8 text-red-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Fee Manager Component -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                @livewire('membership.enhanced-fee-manager')
            </div>
        </div>
    </div>

    <script>
        // Print functionality
        window.addEventListener('beforeprint', function() {
            // Hide unnecessary elements for printing
            document.querySelectorAll('button, .fixed, .modal').forEach(el => {
                el.style.display = 'none';
            });
        });

        window.addEventListener('afterprint', function() {
            // Restore elements after printing
            document.querySelectorAll('button, .fixed, .modal').forEach(el => {
                el.style.display = '';
            });
        });

        // Export functionality
        function exportFees(format) {
            const url = `{{ route('fees.export', ['format' => '']) }}${format}`;
            window.open(url, '_blank');
        }

        // Print invoice/receipt
        function printElement(elementId) {
            const element = document.getElementById(elementId);
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print Document</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .no-print { display: none; }
                    </style>
                </head>
                <body>
                    ${element.innerHTML}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</x-app-layout>