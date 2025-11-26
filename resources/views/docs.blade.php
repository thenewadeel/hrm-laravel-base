@extends('layouts.app')

@section('title', 'Documentation Portal')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-4 py-6 sm:p-6 lg:p-8">
                <header class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        📚 HRM Laravel Base Documentation
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Complete documentation for the HRM Laravel Base ERP system
                    </p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Core Documentation -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            📋 Core Documentation
                        </h2>
                        <div class="space-y-3">
                            <a href="/docs/SRS.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">📋 Software Requirements</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Complete SRS documentation</div>
                            </a>
                            <a href="/docs/big picture.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">📊 Big Picture</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">System overview</div>
                            </a>
                            <a href="/docs/ERD.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">🗄️ Database Design</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">ERD and schema</div>
                            </a>
                            <a href="/docs/project plan.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">📅 Project Plan</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Implementation timeline</div>
                            </a>
                        </div>
                    </div>

                    <!-- Technical Documentation -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            🛠️ Technical Documentation
                        </h2>
                        <div class="space-y-3">
                            <a href="/docs/interfaces spec.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">🔌 Interface Specifications</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">API and interfaces</div>
                            </a>
                            <a href="/docs/list of modules.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">🧩 Module List</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">All modules</div>
                            </a>
                            <a href="/docs/list of routes.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">🛣️ Route List</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">All routes</div>
                            </a>
                            <a href="/docs/workflows.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">⚙️ Workflows</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Business workflows</div>
                            </a>
                        </div>
                    </div>

                    <!-- Management -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            📊 Management
                        </h2>
                        <div class="space-y-3">
                            <a href="/docs/timeline.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">⏰ Timeline</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Project timeline</div>
                            </a>
                            <a href="/docs/project log.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">📝 Project Log</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Development log</div>
                            </a>
                        </div>
                    </div>

                    <!-- Reports -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            📈 Reports & Progress
                        </h2>
                        <div class="space-y-3">
                            <a href="/docs/report-executive-progress.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">📊 Executive Progress</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Management reports</div>
                            </a>
                            <a href="/docs/report-technical-progress.html" class="block p-3 bg-white dark:bg-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                <div class="font-medium text-gray-900 dark:text-white">🔧 Technical Progress</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Technical reports</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Features Section -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        🚀 Key Features
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-blue-50 dark:bg-blue-900 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                                💰 Financial Management
                            </h3>
                            <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                <li>• Complete voucher system</li>
                                <li>• Financial statements</li>
                                <li>• Bank reconciliation</li>
                                <li>• Fixed asset management</li>
                            </ul>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">
                                👥 Human Resources
                            </h3>
                            <ul class="text-sm text-green-700 dark:text-green-300 space-y-1">
                                <li>• Employee management</li>
                                <li>• Payroll processing</li>
                                <li>• Leave management</li>
                                <li>• Attendance tracking</li>
                            </ul>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-2">
                                📦 Inventory Management
                            </h3>
                            <ul class="text-sm text-purple-700 dark:text-purple-300 space-y-1">
                                <li>• Multi-store support</li>
                                <li>• Stock tracking</li>
                                <li>• Transaction management</li>
                                <li>• Low stock alerts</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                        ⚡ Quick Actions
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="/docs/pdf/index.html" class="flex items-center p-4 bg-white dark:bg-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            <div class="text-center">
                                <div class="text-2xl mb-2">📄</div>
                                <div class="font-medium text-gray-900 dark:text-white">Browse All Documentation</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Complete index</div>
                            </div>
                        </a>
                        <button onclick="window.print()" class="flex items-center p-4 bg-white dark:bg-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            <div class="text-center">
                                <div class="text-2xl mb-2">🖨️</div>
                                <div class="font-medium text-gray-900 dark:text-white">Print Page</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Print this overview</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Add some interactivity
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight current section based on scroll
        const sections = document.querySelectorAll('h2');
        const navLinks = document.querySelectorAll('a[href^="/docs/"]');
        
        window.addEventListener('scroll', function() {
            let current = '';
            sections.forEach(section => {
                const rect = section.getBoundingClientRect();
                if (rect.top <= 100 && rect.bottom >= 100) {
                    current = section.textContent.trim();
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('ring-2', 'ring-blue-500');
                if (link.textContent.includes(current)) {
                    link.classList.add('ring-2', 'ring-blue-500');
                }
            });
        });
    });
</script>
@endsection