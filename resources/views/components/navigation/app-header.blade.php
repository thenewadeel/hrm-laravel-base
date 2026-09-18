@props([])

@php
    $user = auth()->user();
    $canSee = fn (array $roles): bool => $user !== null && $user->hasRole($roles);

    $navCommands = [];

    $navCommands[] = ['section' => 'Dashboard', 'label' => 'Dashboard', 'href' => route('dashboard')];

    if ($canSee(['employee', 'manager'])) {
        $navCommands[] = ['section' => 'Self Service', 'label' => 'Employee Dashboard', 'href' => route('portal.employee.dashboard')];
        $navCommands[] = ['section' => 'Self Service', 'label' => 'My Attendance', 'href' => route('portal.employee.attendance')];
        $navCommands[] = ['section' => 'Self Service', 'label' => 'My Leave', 'href' => route('portal.employee.leave')];
        $navCommands[] = ['section' => 'Self Service', 'label' => 'My Payslips', 'href' => route('portal.employee.payslips')];

        if ($canSee(['manager'])) {
            $navCommands[] = ['section' => 'Self Service', 'label' => 'Manager Dashboard', 'href' => route('portal.manager.dashboard')];
            $navCommands[] = ['section' => 'Self Service', 'label' => 'Team Attendance', 'href' => route('portal.manager.team-attendance')];
            $navCommands[] = ['section' => 'Self Service', 'label' => 'Approve Reports', 'href' => route('portal.manager.reports')];
        }
    }

    $navCommands = array_merge($navCommands, [
        ['section' => 'Inventory', 'label' => 'Items', 'href' => route('inventory.items.index')],
        ['section' => 'Inventory', 'label' => 'Stores', 'href' => route('inventory.stores.index')],
        ['section' => 'Inventory', 'label' => 'Transactions', 'href' => route('inventory.transactions.index')],
        ['section' => 'Inventory', 'label' => 'Transaction Wizard', 'href' => route('inventory.transactions.wizard')],
        ['section' => 'Inventory', 'label' => 'Stock Dashboard', 'href' => route('inventory.stock.index')],
        ['section' => 'Inventory', 'label' => 'Stock Adjustment', 'href' => route('inventory.stock.adjustment')],
        ['section' => 'Inventory', 'label' => 'Stock Count', 'href' => route('inventory.stock.count')],
        ['section' => 'Inventory', 'label' => 'Stock Transfer', 'href' => route('inventory.stock.transfer')],
        ['section' => 'Inventory', 'label' => 'Stock Levels Report', 'href' => route('inventory.reports.stock-levels')],
        ['section' => 'Inventory', 'label' => 'Low Stock', 'href' => route('inventory.reports.low-stock')],
        ['section' => 'Inventory', 'label' => 'Stock Movement', 'href' => route('inventory.reports.movement')],
        ['section' => 'Accounting & Finance', 'label' => 'Chart of Accounts', 'href' => route('accounting.index')],
        ['section' => 'Accounting & Finance', 'label' => 'Sales Voucher', 'href' => route('accounting.vouchers.sales.create')],
        ['section' => 'Accounting & Finance', 'label' => 'Purchase Voucher', 'href' => route('accounting.vouchers.purchase.create')],
        ['section' => 'Accounting & Finance', 'label' => 'Expense Voucher', 'href' => route('accounting.vouchers.expense.create')],
        ['section' => 'Accounting & Finance', 'label' => 'Salary Voucher', 'href' => route('accounting.vouchers.salary.create')],
        ['section' => 'Accounting & Finance', 'label' => 'Bank Accounts', 'href' => route('accounting.bank-accounts.index')],
        ['section' => 'Accounting & Finance', 'label' => 'Bank Statements', 'href' => route('accounting.bank-statements.index')],
        ['section' => 'Accounting & Finance', 'label' => 'Bank Reconciliation', 'href' => route('accounting.bank-reconciliation.index')],
        ['section' => 'Accounting & Finance', 'label' => 'Cash Receipts', 'href' => route('accounting.cash-receipts.index')],
        ['section' => 'Accounting & Finance', 'label' => 'Cash Payments', 'href' => route('accounting.cash-payments.index')],
        ['section' => 'Accounting & Finance', 'label' => 'Receivables', 'href' => route('accounting.outstanding.receivables')],
        ['section' => 'Accounting & Finance', 'label' => 'Payables', 'href' => route('accounting.outstanding.payables')],
        ['section' => 'Accounting & Finance', 'label' => 'Fixed Assets', 'href' => route('accounting.fixed-assets.index')],
        ['section' => 'Accounting & Finance', 'label' => 'Financial Years', 'href' => route('accounting.financial-years.index')],
        ['section' => 'Accounting & Finance', 'label' => 'Tax Management', 'href' => route('accounting.tax.reporting.dashboard')],
        ['section' => 'Human Resources', 'label' => 'Employees', 'href' => route('hr.employees.index')],
        ['section' => 'Human Resources', 'label' => 'Job Positions', 'href' => route('hr.positions.index')],
        ['section' => 'Human Resources', 'label' => 'Shifts', 'href' => route('hr.shifts.index')],
        ['section' => 'Human Resources', 'label' => 'Attendance Dashboard', 'href' => route('attendance.dashboard')],
        ['section' => 'Human Resources', 'label' => 'Payroll Dashboard', 'href' => route('payroll.dashboard')],
        ['section' => 'Human Resources', 'label' => 'Payroll Processing', 'href' => route('payroll.processing')],
        ['section' => 'Human Resources', 'label' => 'Salary Advances', 'href' => route('payroll.advances')],
        ['section' => 'Human Resources', 'label' => 'Employee Loans', 'href' => route('payroll.loans')],
        ['section' => 'Human Resources', 'label' => 'Employee Increments', 'href' => route('payroll.increments')],
        ['section' => 'Human Resources', 'label' => 'Payroll Tax', 'href' => route('payroll.tax')],
        ['section' => 'Membership', 'label' => 'Members', 'href' => route('members.index')],
        ['section' => 'Membership', 'label' => 'Membership Dashboard', 'href' => route('membership.dashboard')],
        ['section' => 'Membership', 'label' => 'Membership Cards', 'href' => route('cards.index')],
        ['section' => 'Membership', 'label' => 'Card Templates', 'href' => route('cards.templates')],
        ['section' => 'Membership', 'label' => 'Card Settings', 'href' => route('cards.settings')],
        ['section' => 'Membership', 'label' => 'Fees', 'href' => route('fees.index')],
        ['section' => 'Membership', 'label' => 'Fee Statistics', 'href' => route('fees.statistics')],
        ['section' => 'Membership', 'label' => 'Subscriptions', 'href' => route('subscriptions.index')],
        ['section' => 'Membership', 'label' => 'Subscription Statistics', 'href' => route('subscriptions.statistics')],
        ['section' => 'Membership', 'label' => 'Upcoming Renewals', 'href' => route('subscriptions.expiring')],
        ['section' => 'Organization', 'label' => 'Organization Dashboard', 'href' => route('organization.dashboard')],
        ['section' => 'Organization', 'label' => 'Analytics', 'href' => route('organization.analytics')],
        ['section' => 'Organization', 'label' => 'Organization Structure', 'href' => route('organization.structure')],
        ['section' => 'Organization', 'label' => 'Departments', 'href' => route('organization.units.index')],
        ['section' => 'Organization', 'label' => 'Organizations', 'href' => route('organization.index')],
        ['section' => 'Reports & Analytics', 'label' => 'Trial Balance', 'href' => route('accounting.download.trial-balance')],
        ['section' => 'Reports & Analytics', 'label' => 'Balance Sheet', 'href' => route('accounting.download.balance-sheet')],
        ['section' => 'Reports & Analytics', 'label' => 'Income Statement', 'href' => route('accounting.download.income-statement')],
        ['section' => 'Reports & Analytics', 'label' => 'Stock Levels Report', 'href' => route('inventory.reports.stock-levels')],
        ['section' => 'Reports & Analytics', 'label' => 'Low Stock Report', 'href' => route('inventory.reports.low-stock')],
        ['section' => 'Reports & Analytics', 'label' => 'Stock Movement Report', 'href' => route('inventory.reports.movement')],
        ['section' => 'Reports & Analytics', 'label' => 'Advance Reports', 'href' => route('payroll.advance-reports')],
        ['section' => 'Reports & Analytics', 'label' => 'Attendance Export', 'href' => route('attendance.export-payroll')],
    ]);

    if ($canSee(['admin'])) {
        $navCommands[] = ['section' => 'Admin Portal', 'label' => 'Admin Dashboard', 'href' => route('admin.dashboard')];
        $navCommands[] = ['section' => 'Admin Portal', 'label' => 'Attach User', 'href' => route('admin.attach-user.form')];
    }

    if ($canSee(['admin', 'manager'])) {
        $navCommands[] = ['section' => 'System Setup', 'label' => 'Organization Setup', 'href' => route('setup.organization')];
        $navCommands[] = ['section' => 'System Setup', 'label' => 'Accounts Setup', 'href' => route('setup.accounts')];
        $navCommands[] = ['section' => 'System Setup', 'label' => 'Stores Setup', 'href' => route('setup.stores')];
    }
@endphp

<header class="surface border-b border-secondary sticky top-0 z-30"
        x-data="{
            railOpen: localStorage.getItem('nav-rail') === 'true',
            searchOpen: false,
            search: '',
            searchIndex: -1,
            notifOpen: false,
            userOpen: false,
            mobileUserOpen: false,
            links: @js($navCommands),
            init() {
                this.$watch('railOpen', value => {
                    localStorage.setItem('nav-rail', value);
                });
            },
            get filtered() {
                const q = this.search.trim().toLowerCase();
                if (!q) return this.links;
                return this.links.filter(link => link.label.toLowerCase().includes(q));
            },
            get grouped() {
                const map = {};
                this.filtered.forEach(link => {
                    if (!map[link.section]) map[link.section] = [];
                    map[link.section].push(link);
                });
                return map;
            },
            openSearch() {
                this.searchOpen = true;
                this.notifOpen = false;
                this.userOpen = false;
                this.mobileUserOpen = false;
                this.$nextTick(() => this.$refs.searchInput?.focus());
            },
            closeSearch() {
                this.searchOpen = false;
                this.search = '';
                this.searchIndex = -1;
            },
            moveSearch(direction) {
                if (!this.filtered.length) return;
                this.searchIndex = (this.searchIndex + direction + this.filtered.length) % this.filtered.length;
            },
            openLink() {
                const target = this.filtered[this.searchIndex] || this.filtered[0];
                if (target) window.location.href = target.href;
            }
        }"
        @keydown.escape.window="searchOpen = false; notifOpen = false; userOpen = false; mobileUserOpen = false"
        @keydown.meta.k.window.prevent="openSearch()"
        @keydown.ctrl.k.window.prevent="openSearch()"
        @toggle-rail.window="railOpen = !railOpen"
        @rail-expand.window="railOpen = false"
        role="banner">

    <div class="flex items-center justify-between h-16 px-4 sm:px-6">
        {{-- Left: mobile hamburger + brand + rail toggle --}}
        <div class="flex items-center gap-1 min-w-0">
            <button @click="$dispatch('toggle-nav')"
                    class="nav-icon-btn md:hidden h-9 w-9 inline-flex items-center justify-center rounded-lg"
                    aria-controls="sidebar"
                    aria-label="Toggle navigation">
                <span class="sr-only">Toggle navigation</span>
                <x-heroicon-o-bars-3 class="h-6 w-6" />
            </button>

            <a href="{{ route('dashboard') }}" class="md:hidden flex items-center" aria-label="Wittness Tech">
                <x-application-mark class="h-7 w-auto" />
            </a>

            <button @click="$dispatch('toggle-rail')"
                    class="nav-icon-btn hidden lg:inline-flex h-9 w-9 items-center justify-center rounded-lg"
                    data-sidebar-toggle
                    :aria-label="railOpen ? 'Expand sidebar' : 'Collapse sidebar'">
                <x-heroicon-o-bars-3-bottom-left class="h-5 w-5" />
            </button>
        </div>

        {{-- Desktop actions (must precede the mobile row so the first
             [data-theme-toggle] / [aria-label="User menu"] match is visible) --}}
        <div class="hidden md:flex items-center gap-1">

            {{-- Command search --}}
            <div class="relative">
                <button @click="searchOpen = !searchOpen; notifOpen = false; userOpen = false; mobileUserOpen = false"
                        class="nav-icon-btn h-9 w-9 inline-flex items-center justify-center rounded-lg"
                        aria-label="Search navigation"
                        :aria-expanded="searchOpen">
                    <span class="sr-only">Search navigation</span>
                    <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                </button>

                <div x-show="searchOpen"
                     @click.outside="closeSearch()"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-80 surface border border-secondary rounded-lg shadow-lg z-50 overflow-hidden">
                    <div class="p-3">
                        <input type="text"
                               x-ref="searchInput"
                               x-model="search"
                               @keydown.down.prevent="moveSearch(1)"
                               @keydown.up.prevent="moveSearch(-1)"
                               @keydown.enter.prevent="openLink()"
                               placeholder="Search..."
                               class="nav-icon-input w-full"
                               aria-label="Command search">
                    </div>
                    <div class="border-t border-secondary max-h-96 overflow-y-auto py-2">
                        <template x-if="filtered.length === 0">
                            <p class="px-4 py-4 nav-text-sm text-muted">No results found</p>
                        </template>
                        <template x-for="section in Object.keys(grouped)" :key="section">
                            <div class="py-1">
                                <div class="px-4 pb-1 pt-2 nav-text-tiny text-muted uppercase" x-text="section"></div>
                                <template x-for="link in grouped[section]" :key="link.href">
                                    <a :href="link.href"
                                       x-text="link.label"
                                       @mouseenter="searchIndex = filtered.indexOf(link)"
                                       :class="searchIndex === filtered.indexOf(link) ? 'bg-bg-secondary' : ''"
                                       class="block px-4 py-2 nav-text-sm text-primary hover:bg-bg-secondary transition-colors"></a>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div class="border-t border-secondary hidden sm:flex items-center gap-4 px-4 py-2">
                        <span class="nav-text-tiny text-muted">↵ Open</span>
                        <span class="nav-text-tiny text-muted">↑↓ Navigate</span>
                        <span class="nav-text-tiny text-muted">esc Close</span>
                        <span class="ml-auto nav-text-tiny text-muted">Ctrl K</span>
                    </div>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="relative">
                <button @click="notifOpen = !notifOpen; searchOpen = false; userOpen = false; mobileUserOpen = false"
                        class="nav-icon-btn h-9 w-9 inline-flex items-center justify-center rounded-lg"
                        aria-label="Notifications"
                        :aria-expanded="notifOpen">
                    <span class="sr-only">View notifications</span>
                    <x-heroicon-o-bell class="h-5 w-5" />
                </button>

                <div x-show="notifOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="notifOpen = false"
                     class="absolute right-0 mt-2 w-80 surface border border-secondary rounded-lg shadow-lg z-50">
                    <div class="px-4 py-3 border-b border-secondary">
                        <h3 class="nav-text-sm font-semibold text-primary">Notifications</h3>
                    </div>
                    <div class="px-4 py-8 text-center">
                        <p class="nav-text-sm text-muted">No new notifications</p>
                    </div>
                </div>
            </div>

            {{-- Language switcher --}}
            <x-navigation.language-switcher />

            {{-- Theme toggle --}}
            <x-navigation.theme-toggle />

            {{-- User menu --}}
            <div class="relative">
                <button @click="userOpen = !userOpen; searchOpen = false; notifOpen = false; mobileUserOpen = false"
                        class="nav-user-trigger flex items-center gap-2 rounded-lg"
                        :aria-expanded="userOpen"
                        aria-label="User menu">
                    @auth
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <img class="avatar h-8 w-8 rounded-full object-cover"
                                 src="{{ Auth::user()->profile_photo_url }}"
                                 alt="{{ Auth::user()->name }}">
                        @else
                            <span class="avatar inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary text-primary-contrast nav-text-sm font-medium">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </span>
                        @endif
                        <span class="hidden lg:inline nav-text-sm font-medium text-primary">{{ Auth::user()->name }}</span>
                        <x-heroicon-o-chevron-down class="h-4 w-4 text-muted" />
                    @endauth
                </button>

                <div x-show="userOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="userOpen = false"
                     class="absolute right-0 mt-2 w-60 surface border border-secondary rounded-lg shadow-lg z-50">
                    @auth
                        <div class="px-4 py-3 border-b border-secondary">
                            <p class="nav-text-sm font-medium text-primary">{{ Auth::user()->name }}</p>
                            <p class="nav-text-xs text-muted truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <div class="py-1">
                            <a href="{{ route('profile.show') }}"
                               class="block px-4 py-2 nav-text-sm text-primary hover:bg-bg-secondary transition-colors">
                                Profile
                            </a>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <a href="{{ route('api-tokens.index') }}"
                                   class="block px-4 py-2 nav-text-sm text-primary hover:bg-bg-secondary transition-colors">
                                    API Tokens
                                </a>
                            @endif
                        </div>

                        <div class="border-t border-secondary py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full text-left px-4 py-2 nav-text-sm text-primary hover:bg-bg-secondary transition-colors">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Mobile actions: theme toggle + compact user menu --}}
        <div class="flex items-center gap-1 md:hidden">
            <x-navigation.theme-toggle />

            <div class="relative">
                <button @click="mobileUserOpen = !mobileUserOpen; searchOpen = false; notifOpen = false; userOpen = false"
                        class="nav-user-trigger flex items-center rounded-lg"
                        :aria-expanded="mobileUserOpen"
                        aria-label="User menu">
                    @auth
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <img class="avatar h-7 w-7 rounded-full object-cover"
                                 src="{{ Auth::user()->profile_photo_url }}"
                                 alt="{{ Auth::user()->name }}">
                        @else
                            <span class="avatar inline-flex items-center justify-center h-7 w-7 rounded-full bg-primary text-primary-contrast nav-text-sm font-medium">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </span>
                        @endif
                    @endauth
                </button>

                <div x-show="mobileUserOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="mobileUserOpen = false"
                     class="absolute right-0 mt-2 w-60 surface border border-secondary rounded-lg shadow-lg z-50">
                    @auth
                        <div class="px-4 py-3 border-b border-secondary">
                            <p class="nav-text-sm font-medium text-primary">{{ Auth::user()->name }}</p>
                            <p class="nav-text-xs text-muted truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <div class="py-1">
                            <a href="{{ route('profile.show') }}"
                               class="block px-4 py-2 nav-text-sm text-primary hover:bg-bg-secondary transition-colors">
                                Profile
                            </a>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <a href="{{ route('api-tokens.index') }}"
                                   class="block px-4 py-2 nav-text-sm text-primary hover:bg-bg-secondary transition-colors">
                                    API Tokens
                                </a>
                            @endif
                        </div>

                        <div class="border-t border-secondary py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full text-left px-4 py-2 nav-text-sm text-primary hover:bg-bg-secondary transition-colors">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</header>