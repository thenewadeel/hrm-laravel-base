<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Badges
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                        Badge System Showcase
                    </h1>

                    <!-- Basic Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Basic Badges</h2>
                        <div class="space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <x-ui-badge>Default</x-ui-badge>
                                <x-ui-badge color="gray">Gray</x-ui-badge>
                                <x-ui-badge color="red">Red</x-ui-badge>
                                <x-ui-badge color="yellow">Yellow</x-ui-badge>
                                <x-ui-badge color="green">Green</x-ui-badge>
                                <x-ui-badge color="blue">Blue</x-ui-badge>
                                <x-ui-badge color="indigo">Indigo</x-ui-badge>
                                <x-ui-badge color="purple">Purple</x-ui-badge>
                                <x-ui-badge color="pink">Pink</x-ui-badge>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <x-ui-badge variant="outline" color="blue">Outline</x-ui-badge>
                                <x-ui-badge variant="subtle" color="green">Subtle</x-ui-badge>
                                <x-ui-badge variant="solid" color="red">Solid</x-ui-badge>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <x-ui-badge size="sm">Small</x-ui-badge>
                                <x-ui-badge size="md">Medium</x-ui-badge>
                                <x-ui-badge size="lg">Large</x-ui-badge>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <x-ui-badge icon="user" color="blue">With Icon</x-ui-badge>
                                <x-ui-badge dot color="green">With Dot</x-ui-badge>
                                <x-ui-badge dismissible color="red">Dismissible</x-ui-badge>
                            </div>
                        </div>
                    </div>

                    <!-- Status Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Status Badges</h2>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">General Status
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-badge status="active" />
                                    <x-badge status="inactive" />
                                    <x-badge status="pending" />
                                    <x-badge status="draft" />
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Financial Status
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-badge status="posted" />
                                    <x-badge status="unposted" />
                                    <x-badge status="void" />
                                    <x-badge status="reconciled" />
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">HR Status</h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-badge status="present" />
                                    <x-badge status="absent" />
                                    <x-badge status="leave" />
                                    <x-badge status="holiday" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Category Badges</h2>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Accounting
                                    Categories</h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui-category-badge category="asset" />
                                    <x-ui-category-badge category="liability" />
                                    <x-ui-category-badge category="equity" />
                                    <x-ui-category-badge category="revenue" />
                                    <x-ui-category-badge category="expense" />
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Transaction Types
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui-category-badge category="sales" />
                                    <x-ui-category-badge category="purchase" />
                                    <x-ui-category-badge category="payment" />
                                    <x-ui-category-badge category="receipt" />
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Priority Levels
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui-category-badge category="low" />
                                    <x-ui-category-badge category="medium" />
                                    <x-ui-category-badge category="high" />
                                    <x-ui-category-badge category="critical" />
                                    <x-ui-category-badge category="urgent" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Count Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Count Badges</h2>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <button class="px-4 py-2 bg-blue-500 text-white rounded-lg">Notifications</button>
                                    <x-ui-count-badge count="5" />
                                </div>
                                <div class="relative">
                                    <button class="px-4 py-2 bg-green-500 text-white rounded-lg">Messages</button>
                                    <x-ui-count-badge count="150" />
                                </div>
                                <div class="relative">
                                    <button class="px-4 py-2 bg-red-500 text-white rounded-lg">Alerts</button>
                                    <x-ui-count-badge count="1" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Key-Value Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Key-Value Badges</h2>
                        <div class="space-y-2">
                            <x-ui-key-value-badge label="Status" value="Active" color="green" />
                            <x-ui-key-value-badge label="Priority" value="High" color="red" />
                            <x-ui-key-value-badge label="Department" value="Finance" color="blue" />
                            <x-ui-key-value-badge label="Role" value="Administrator" color="purple" />
                        </div>
                    </div>

                    <!-- Progress Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Progress Badges</h2>
                        <div class="space-y-4 max-w-md">
                            <x-ui-progress-badge progress="75" />
                            <x-ui-progress-badge progress="30" />
                            <x-ui-progress-badge progress="100" />
                            <x-ui-progress-badge progress="45" />
                        </div>
                    </div>

                    <!-- Tag Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Tag Badges</h2>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Basic Tags</h3>
                                <x-ui-tag-badges :tags="['PHP', 'Laravel', 'Vue.js', 'Tailwind CSS']" />
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Limited Tags</h3>
                                <x-ui-tag-badges :tags="['JavaScript', 'Python', 'React', 'Angular', 'Vue', 'Svelte', 'Next.js']" limit="3" />
                            </div>
                        </div>
                    </div>

                    <!-- Role Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Role Badges</h2>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">System Roles</h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui-role-badge role="super_admin" />
                                    <x-ui-role-badge role="admin" />
                                    <x-ui-role-badge role="manager" />
                                    <x-ui-role-badge role="supervisor" />
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Department Roles
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui-role-badge role="hr_manager" />
                                    <x-ui-role-badge role="accountant" />
                                    <x-ui-role-badge role="developer" />
                                    <x-ui-role-badge role="sales_rep" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Priority Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Priority Badges</h2>
                        <div class="space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <x-ui-priority-badge priority="low" />
                                <x-ui-priority-badge priority="medium" />
                                <x-ui-priority-badge priority="high" />
                                <x-ui-priority-badge priority="critical" />
                                <x-ui-priority-badge priority="urgent" />
                            </div>
                        </div>
                    </div>

                    <!-- Type Badges -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Type Badges</h2>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Document Types
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui-type-badge type="pdf" />
                                    <x-ui-type-badge type="excel" />
                                    <x-ui-type-badge type="word" />
                                    <x-ui-type-badge type="image" />
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Data Types</h3>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui-type-badge type="string" />
                                    <x-ui-type-badge type="number" />
                                    <x-ui-type-badge type="date" />
                                    <x-ui-type-badge type="email" />
                                    <x-ui-type-badge type="currency" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dark Mode Preview -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Dark Mode
                            Compatibility</h2>
                        <div class="bg-gray-900 dark:bg-gray-800 p-6 rounded-lg">
                            <div class="space-y-4">
                                <div class="flex flex-wrap gap-2">
                                    <x-ui-badge color="green">Light Mode</x-ui-badge>
                                    <x-ui-badge color="blue">Dark Mode</x-ui-badge>
                                    <x-badge status="active" />
                                    <x-ui-category-badge category="high" />
                                    <x-ui-role-badge role="admin" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
