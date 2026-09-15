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
                    <x-heroicon-o-arrow-left class="h-5 w-5 inline mr-2" />
                    Back to Basic
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                    <x-heroicon-o-square-2-stack class="h-5 w-5 inline mr-2" />
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
                            <x-heroicon-o-face-smile class="h-8 w-8 text-blue-100" />
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
                            <x-heroicon-o-document-duplicate class="h-8 w-8 text-green-100" />
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
                            <x-heroicon-o-clock class="h-8 w-8 text-orange-100" />
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
                            <x-heroicon-o-clock class="h-8 w-8 text-red-100" />
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