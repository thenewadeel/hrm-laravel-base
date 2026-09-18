@php
    $currentRoute = request()->route() ? request()->route()->getName() : 'dashboard';
    $currentModule = explode('.', $currentRoute)[0] ?? 'dashboard';
    $user = auth()->user();
    $canSee = fn (array $roles): bool => $user !== null && $user->hasRole($roles);

    $sections = [
        'self' => [
            'label' => 'Self Service',
            'icon' => 'user-circle',
            'roles' => ['employee', 'manager'],
            'children' => [
                [
                    'label' => 'Employee Portal',
                    'items' => [
                        ['label' => 'Employee Dashboard', 'icon' => 'home', 'href' => route('portal.employee.dashboard'), 'routes' => ['portal.employee.dashboard']],
                        ['label' => 'My Attendance', 'icon' => 'clock', 'href' => route('portal.employee.attendance'), 'routes' => ['portal.employee.attendance']],
                        ['label' => 'My Leave', 'icon' => 'calendar-days', 'href' => route('portal.employee.leave'), 'routes' => ['portal.employee.leave']],
                        ['label' => 'My Payslips', 'icon' => 'document-text', 'href' => route('portal.employee.payslips'), 'routes' => ['portal.employee.payslips']],
                    ],
                ],
                [
                    'label' => 'Manager Portal',
                    'roles' => ['manager'],
                    'items' => [
                        ['label' => 'Manager Dashboard', 'icon' => 'briefcase', 'href' => route('portal.manager.dashboard'), 'routes' => ['portal.manager.dashboard']],
                        ['label' => 'Team Attendance', 'icon' => 'user-group', 'href' => route('portal.manager.team-attendance'), 'routes' => ['portal.manager.team-attendance']],
                        ['label' => 'Approve Reports', 'icon' => 'document-chart-bar', 'href' => route('portal.manager.reports'), 'routes' => ['portal.manager.reports']],
                    ],
                ],
            ],
        ],
        'inventory' => [
            'label' => 'Inventory',
            'icon' => 'cube',
            'children' => [
                [
                    'label' => 'Catalogue',
                    'items' => [
                        ['label' => 'Dashboard', 'icon' => 'chart-pie', 'href' => route('inventory.dashboard'), 'routes' => ['inventory.dashboard']],
                        ['label' => 'Items', 'icon' => 'cube', 'href' => route('inventory.items.index'), 'routes' => ['inventory.items.*']],
                        ['label' => 'Stores', 'icon' => 'building-storefront', 'href' => route('inventory.stores.index'), 'routes' => ['inventory.stores.*']],
                    ],
                ],
                [
                    'label' => 'Stock Operations',
                    'items' => [
                        ['label' => 'Transactions', 'icon' => 'queue-list', 'href' => route('inventory.transactions.index'), 'routes' => ['inventory.transactions.*']],
                        ['label' => 'Transaction Wizard', 'icon' => 'command-line', 'href' => route('inventory.transactions.wizard'), 'routes' => ['inventory.transactions.wizard']],
                        ['label' => 'Stock Dashboard', 'icon' => 'chart-bar-square', 'href' => route('inventory.stock.index'), 'routes' => ['inventory.stock.*']],
                        ['label' => 'Stock Adjustment', 'icon' => 'scale', 'href' => route('inventory.stock.adjustment'), 'routes' => ['inventory.stock.adjustment']],
                        ['label' => 'Stock Count', 'icon' => 'calculator', 'href' => route('inventory.stock.count'), 'routes' => ['inventory.stock.count']],
                        ['label' => 'Stock Transfer', 'icon' => 'arrows-right-left', 'href' => route('inventory.stock.transfer'), 'routes' => ['inventory.stock.transfer']],
                    ],
                ],
                [
                    'label' => 'Insights',
                    'items' => [
                        ['label' => 'Stock Levels', 'icon' => 'chart-bar', 'href' => route('inventory.reports.stock-levels'), 'routes' => ['inventory.reports.stock-levels']],
                        ['label' => 'Low Stock', 'icon' => 'hand-raised', 'href' => route('inventory.reports.low-stock'), 'routes' => ['inventory.reports.low-stock']],
                        ['label' => 'Stock Movement', 'icon' => 'arrow-trending-up', 'href' => route('inventory.reports.movement'), 'routes' => ['inventory.reports.movement']],
                    ],
                ],
            ],
        ],
        'accounting' => [
            'label' => 'Accounting & Finance',
            'icon' => 'banknotes',
            'children' => [
                [
                    'label' => 'Accounting',
                    'items' => [
                        ['label' => 'Chart of Accounts', 'icon' => 'book-open', 'href' => route('accounting.index'), 'routes' => ['accounting.index', 'accounts.*']],
                    ],
                ],
                [
                    'label' => 'Vouchers',
                    'items' => [
                        ['label' => 'Sales Voucher', 'icon' => 'receipt-percent', 'href' => route('accounting.vouchers.sales.create'), 'routes' => ['accounting.vouchers.sales.*']],
                        ['label' => 'Purchase Voucher', 'icon' => 'shopping-cart', 'href' => route('accounting.vouchers.purchase.create'), 'routes' => ['accounting.vouchers.purchase.*']],
                        ['label' => 'Expense Voucher', 'icon' => 'banknotes', 'href' => route('accounting.vouchers.expense.create'), 'routes' => ['accounting.vouchers.expense.*']],
                        ['label' => 'Salary Voucher', 'icon' => 'currency-dollar', 'href' => route('accounting.vouchers.salary.create'), 'routes' => ['accounting.vouchers.salary.*']],
                    ],
                ],
                [
                    'label' => 'Bank & Cash',
                    'items' => [
                        ['label' => 'Bank Accounts', 'icon' => 'building-library', 'href' => route('accounting.bank-accounts.index'), 'routes' => ['accounting.bank-accounts.*']],
                        ['label' => 'Bank Statements', 'icon' => 'document-text', 'href' => route('accounting.bank-statements.index'), 'routes' => ['accounting.bank-statements.*']],
                        ['label' => 'Bank Reconciliation', 'icon' => 'arrows-right-left', 'href' => route('accounting.bank-reconciliation.index'), 'routes' => ['accounting.bank-reconciliation.*']],
                        ['label' => 'Cash Receipts', 'icon' => 'arrow-down-tray', 'href' => route('accounting.cash-receipts.index'), 'routes' => ['accounting.cash-receipts.*']],
                        ['label' => 'Cash Payments', 'icon' => 'arrow-up-tray', 'href' => route('accounting.cash-payments.index'), 'routes' => ['accounting.cash-payments.*']],
                    ],
                ],
                [
                    'label' => 'Receivables & Payables',
                    'items' => [
                        ['label' => 'Receivables', 'icon' => 'arrow-trending-up', 'href' => route('accounting.outstanding.receivables'), 'routes' => ['accounting.outstanding.receivables']],
                        ['label' => 'Payables', 'icon' => 'arrow-trending-down', 'href' => route('accounting.outstanding.payables'), 'routes' => ['accounting.outstanding.payables']],
                    ],
                ],
                [
                    'label' => 'Assets & Tax',
                    'items' => [
                        ['label' => 'Fixed Assets', 'icon' => 'building-office', 'href' => route('accounting.fixed-assets.index'), 'routes' => ['accounting.fixed-assets.*']],
                        ['label' => 'Financial Years', 'icon' => 'calendar-days', 'href' => route('accounting.financial-years.index'), 'routes' => ['accounting.financial-years.*']],
                        ['label' => 'Tax Management', 'icon' => 'lock-closed', 'href' => route('accounting.tax.reporting.dashboard'), 'routes' => ['accounting.tax.*']],
                    ],
                ],
            ],
        ],
        'hr' => [
            'label' => 'Human Resources',
            'icon' => 'users',
            'children' => [
                [
                    'label' => 'Employee Management',
                    'items' => [
                        ['label' => 'Employees', 'icon' => 'user-group', 'href' => route('hr.employees.index'), 'routes' => ['hr.employees.*']],
                        ['label' => 'Job Positions', 'icon' => 'briefcase', 'href' => route('hr.positions.index'), 'routes' => ['hr.positions.*']],
                        ['label' => 'Shifts', 'icon' => 'clock', 'href' => route('hr.shifts.index'), 'routes' => ['hr.shifts.*']],
                    ],
                ],
                [
                    'label' => 'Attendance Management',
                    'items' => [
                        ['label' => 'Attendance Dashboard', 'icon' => 'calendar-days', 'href' => route('attendance.dashboard'), 'routes' => ['attendance.*']],
                    ],
                ],
                [
                    'label' => 'Payroll Management',
                    'items' => [
                        ['label' => 'Payroll Dashboard', 'icon' => 'chart-bar-square', 'href' => route('payroll.dashboard'), 'routes' => ['payroll.dashboard']],
                        ['label' => 'Payroll Processing', 'icon' => 'cog-6-tooth', 'href' => route('payroll.processing'), 'routes' => ['payroll.processing']],
                        ['label' => 'Salary Advances', 'icon' => 'arrow-trending-up', 'href' => route('payroll.advances'), 'routes' => ['payroll.advances']],
                        ['label' => 'Employee Loans', 'icon' => 'banknotes', 'href' => route('payroll.loans'), 'routes' => ['payroll.loans']],
                        ['label' => 'Employee Increments', 'icon' => 'clipboard-document-check', 'href' => route('payroll.increments'), 'routes' => ['payroll.increments']],
                        ['label' => 'Payroll Tax', 'icon' => 'lock-closed', 'href' => route('payroll.tax'), 'routes' => ['payroll.tax']],
                    ],
                ],
            ],
        ],
        'membership' => [
            'label' => 'Membership',
            'icon' => 'identification',
            'children' => [
                [
                    'label' => 'Members',
                    'items' => [
                        ['label' => 'Members', 'icon' => 'identification', 'href' => route('members.index'), 'routes' => ['members.*', 'api.members.*']],
                        ['label' => 'Membership Dashboard', 'icon' => 'building-office-2', 'href' => route('membership.dashboard'), 'routes' => ['membership.dashboard']],
                    ],
                ],
                [
                    'label' => 'Cards',
                    'items' => [
                        ['label' => 'Membership Cards', 'icon' => 'credit-card', 'href' => route('cards.index'), 'routes' => ['cards.*']],
                        ['label' => 'Card Templates', 'icon' => 'swatch', 'href' => route('cards.templates'), 'routes' => ['cards.templates']],
                        ['label' => 'Card Settings', 'icon' => 'cog-6-tooth', 'href' => route('cards.settings'), 'routes' => ['cards.settings']],
                    ],
                ],
                [
                    'label' => 'Fees',
                    'items' => [
                        ['label' => 'Fees', 'icon' => 'banknotes', 'href' => route('fees.index'), 'routes' => ['fees.*']],
                        ['label' => 'Fee Statistics', 'icon' => 'chart-bar', 'href' => route('fees.statistics'), 'routes' => ['fees.statistics']],
                    ],
                ],
                [
                    'label' => 'Subscriptions',
                    'items' => [
                        ['label' => 'Subscriptions', 'icon' => 'calendar-days', 'href' => route('subscriptions.index'), 'routes' => ['subscriptions.*']],
                        ['label' => 'Subscription Statistics', 'icon' => 'chart-pie', 'href' => route('subscriptions.statistics'), 'routes' => ['subscriptions.statistics']],
                        ['label' => 'Upcoming Renewals', 'icon' => 'hand-raised', 'href' => route('subscriptions.expiring'), 'routes' => ['subscriptions.expiring']],
                    ],
                ],
            ],
        ],
        'organization' => [
            'label' => 'Organization',
            'icon' => 'building-office',
            'children' => [
                [
                    'label' => 'Overview',
                    'items' => [
                        ['label' => 'Organization Dashboard', 'icon' => 'chart-bar-square', 'href' => route('organization.dashboard'), 'routes' => ['organization.dashboard']],
                        ['label' => 'Analytics', 'icon' => 'chart-pie', 'href' => route('organization.analytics'), 'routes' => ['organization.analytics']],
                    ],
                ],
                [
                    'label' => 'Structure',
                    'items' => [
                        ['label' => 'Organization Structure', 'icon' => 'rectangle-group', 'href' => route('organization.structure'), 'routes' => ['organization.structure']],
                        ['label' => 'Departments', 'icon' => 'building-office-2', 'href' => route('organization.units.index'), 'routes' => ['organization.units.*']],
                    ],
                ],
                [
                    'label' => 'Manage',
                    'items' => [
                        ['label' => 'Organizations', 'icon' => 'building-library', 'href' => route('organization.index'), 'routes' => ['organization.index']],
                    ],
                ],
            ],
        ],
        'reports' => [
            'label' => 'Reports & Analytics',
            'icon' => 'chart-bar',
            'children' => [
                [
                    'label' => 'Financial',
                    'items' => [
                        ['label' => 'Trial Balance', 'icon' => 'scale', 'href' => route('accounting.download.trial-balance'), 'routes' => ['accounting.download.trial-balance']],
                        ['label' => 'Balance Sheet', 'icon' => 'document-chart-bar', 'href' => route('accounting.download.balance-sheet'), 'routes' => ['accounting.download.balance-sheet']],
                        ['label' => 'Income Statement', 'icon' => 'chart-bar', 'href' => route('accounting.download.income-statement'), 'routes' => ['accounting.download.income-statement']],
                    ],
                ],
                [
                    'label' => 'Inventory',
                    'items' => [
                        ['label' => 'Stock Levels Report', 'icon' => 'cube', 'href' => route('inventory.reports.stock-levels'), 'routes' => ['inventory.reports.stock-levels']],
                        ['label' => 'Low Stock Report', 'icon' => 'hand-raised', 'href' => route('inventory.reports.low-stock'), 'routes' => ['inventory.reports.low-stock']],
                        ['label' => 'Stock Movement Report', 'icon' => 'arrow-trending-up', 'href' => route('inventory.reports.movement'), 'routes' => ['inventory.reports.movement']],
                    ],
                ],
                [
                    'label' => 'Payroll & HR',
                    'items' => [
                        ['label' => 'Advance Reports', 'icon' => 'banknotes', 'href' => route('payroll.advance-reports'), 'routes' => ['payroll.advance-reports']],
                        ['label' => 'Attendance Export', 'icon' => 'document-arrow-down', 'href' => route('attendance.export-payroll'), 'routes' => ['attendance.export-payroll']],
                    ],
                ],
            ],
        ],
        'admin' => [
            'label' => 'Admin Portal',
            'icon' => 'shield-check',
            'roles' => ['admin'],
            'children' => [
                [
                    'label' => null,
                    'items' => [
                        ['label' => 'Admin Dashboard', 'icon' => 'chart-bar-square', 'href' => route('admin.dashboard'), 'routes' => ['admin.dashboard']],
                        ['label' => 'Attach User', 'icon' => 'user-plus', 'href' => route('admin.attach-user.form'), 'routes' => ['admin.attach-user.form']],
                    ],
                ],
            ],
        ],
        'setup' => [
            'label' => 'System Setup',
            'icon' => 'wrench-screwdriver',
            'roles' => ['admin', 'manager'],
            'children' => [
                [
                    'label' => 'Setup',
                    'items' => [
                        ['label' => 'Organization Setup', 'icon' => 'building-office', 'href' => route('setup.organization'), 'routes' => ['setup.organization']],
                        ['label' => 'Accounts Setup', 'icon' => 'book-open', 'href' => route('setup.accounts'), 'routes' => ['setup.accounts']],
                        ['label' => 'Stores Setup', 'icon' => 'building-storefront', 'href' => route('setup.stores'), 'routes' => ['setup.stores']],
                    ],
                ],
            ],
        ],
    ];

    // Role-gate sections and subgroups.
    foreach ($sections as $sectionsKey => $section) {
        $sections[$sectionsKey]['key'] = $sectionsKey;
    }
    $sections = array_values(array_filter($sections, fn ($section) => ! isset($section['roles']) || $canSee($section['roles'])));

    foreach ($sections as &$section) {
        $section['children'] = array_values(array_filter(
            $section['children'] ?? [],
            fn ($group) => ! isset($group['roles']) || $canSee($group['roles'])
        ));
        $section['active'] = false;

        foreach ($section['children'] as &$group) {
            foreach ($group['items'] as &$item) {
                $item['active'] = request()->routeIs($item['routes']);
                $section['active'] = $section['active'] || $item['active'];
            }
            unset($item);
        }
        unset($group);
    }
    unset($section);

    // Search index: section key -> terms to match against.
    $navSearchData = [];
    foreach ($sections as $section) {
        $terms = [$section['label']];
        foreach ($section['children'] ?? [] as $group) {
            foreach ($group['items'] as $item) {
                $terms[] = $item['label'];
            }
        }
        $navSearchData[$section['key']] = array_values(array_filter($terms));
    }

    // Default open state based on the current module.
    $expandedDefaults = [
        'self' => $currentModule === 'portal',
        'inventory' => $currentModule === 'inventory',
        'accounting' => in_array($currentModule, ['accounts', 'accounting'], true),
        'hr' => in_array($currentModule, ['hr', 'hrm', 'attendance', 'payroll'], true),
        'membership' => in_array($currentModule, ['members', 'membership', 'cards', 'fees', 'subscriptions'], true),
        'organization' => $currentModule === 'organization',
        'reports' => str_contains($currentRoute, 'report'),
        'admin' => $currentModule === 'admin',
        'setup' => $currentModule === 'setup',
    ];
    $expandedDefaults = array_filter($expandedDefaults, fn ($key) => array_key_exists($key, $navSearchData), ARRAY_FILTER_USE_KEY);
@endphp

<div class="px-3 pt-3 pb-4"
     x-data="{
        search: '',
        searchData: @js($navSearchData),
        expandedSections: Object.assign(@js($expandedDefaults), JSON.parse(localStorage.getItem('nav-sections') || '{}')),
        toggleSection(section) {
            this.expandedSections[section] = !this.expandedSections[section];
        },
        sectionMatch(key, label) {
            if (!this.search) return true;
            const q = this.search.trim().toLowerCase();
            if (!q) return true;
            const terms = this.searchData[key] || [label];
            return terms.some(term => String(term).toLowerCase().includes(q));
        },
        visible(key, sectionLabel, groupLabel, itemLabel) {
            if (!this.search) return true;
            const q = this.search.trim().toLowerCase();
            if (!q) return true;
            return [sectionLabel, groupLabel, itemLabel].some(term => String(term || '').toLowerCase().includes(q));
        },
        init() {
            this.$watch('search', value => {
                const q = (value || '').trim().toLowerCase();
                if (!q) return;
                Object.keys(this.searchData).forEach(key => {
                    if (this.searchData[key].some(term => String(term).toLowerCase().includes(q))) {
                        this.expandedSections[key] = true;
                    }
                });
            });
            this.$watch('expandedSections', value => {
                localStorage.setItem('nav-sections', JSON.stringify(value));
            }, { deep: true });
        }
     }">

    {{-- Rail mode: icon-only top level quick switch --}}
    <div class="max-lg:hidden px-2 pb-2">
        <nav x-show="railOpen" class="flex flex-col items-center gap-1" aria-label="Rail navigation">
            @foreach ($sections as $section)
                <button type="button"
                        :aria-label="'{{ $section['label'] }}'"
                        :title="'{{ $section['label'] }}'"
                        class="nav-icon-btn {{ $section['active'] ? 'nav-item-active' : '' }} h-9 w-9 inline-flex items-center justify-center rounded-lg"
                        @click="$dispatch('rail-expand'); toggleSection('{{ $section['key'] }}')">
                    <x-dynamic-component :component="'heroicon-m-'.$section['icon']" class="h-5 w-5" />
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Full navigation (hidden in rail / desktop) --}}
    <div :class="railOpen ? 'lg:hidden' : ''">
        {{-- Quick Search --}}
        <div class="relative mb-3">
            <input type="text"
                   placeholder="Search navigation…"
                   class="nav-icon-input w-full pl-8"
                   x-model="search"
                   aria-label="Search navigation">
            <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-2 h-4 w-4 text-muted pointer-events-none" />
        </div>

        <nav class="space-y-1" aria-label="Sidebar">
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               x-show="!search || sectionMatch('dashboard', 'Dashboard')"
               :class="railOpen ? 'lg:hidden' : ''"
               class="nav-item nav-link {{ request()->routeIs('dashboard') ? 'nav-item-active nav-link-active bg-primary text-primary-contrast' : 'text-secondary hover:bg-bg-secondary hover:text-primary' }} flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors"
               {{ request()->routeIs('dashboard') ? 'aria-current="page"' : '' }}>
                <span class="flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-squares-2x2 class="h-5 w-5" />
                </span>
                <span class="nav-text-sm">Dashboard</span>
            </a>

            {{-- Accordion sections --}}
            @foreach ($sections as $section)
                <div class="nav-section" x-show="sectionMatch('{{ $section['key'] }}', '{{ $section['label'] }}')">
                    <button type="button"
                            @click="toggleSection('{{ $section['key'] }}')"
                            @keydown.enter.prevent="toggleSection('{{ $section['key'] }}')"
                            class="nav-item nav-section-trigger {{ $section['active'] ? 'nav-item-active' : '' }} w-full flex items-center gap-2.5 rounded-lg transition-colors"
                            :aria-expanded="expandedSections['{{ $section['key'] }}']"
                            aria-controls="section-{{ $section['key'] }}">
                        <span class="flex items-center justify-center flex-shrink-0">
                            <x-dynamic-component :component="'heroicon-o-'.$section['icon']" class="h-5 w-5" />
                        </span>
                        <span class="flex-1 text-left nav-text-sm" :class="railOpen ? 'lg:hidden' : ''">{{ $section['label'] }}</span>
                        <span class="inline-flex flex-shrink-0 transition-transform transform"
                              x-bind:class="(expandedSections['{{ $section['key'] }}'] ? 'rotate-180' : '') + (railOpen ? ' lg:hidden' : '')">
                            <x-heroicon-o-chevron-down class="h-4 w-4" />
                        </span>
                    </button>

                    <div x-show="expandedSections['{{ $section['key'] }}']"
                         x-transition
                         id="section-{{ $section['key'] }}"
                         class="mt-1 space-y-0.5">
                        @foreach ($section['children'] as $group)
                            @if (isset($group['label']) && $group['label'])
                                <div class="px-4 pt-3 pb-1"
                                     x-show="visible('{{ $section['key'] }}', '{{ $section['label'] }}', '{{ $group['label'] }}', '{{ $group['label'] }}')">
                                    <div class="nav-text-tiny text-muted uppercase">{{ $group['label'] }}</div>
                                </div>
                            @endif

                            @foreach ($group['items'] as $item)
                                <a href="{{ $item['href'] }}"
                                   x-show="visible('{{ $section['key'] }}', '{{ $section['label'] }}', '{{ $group['label'] ?? '' }}', '{{ $item['label'] }}')"
                                   class="nav-item nav-link nav-child{{ $item['active'] ? ' nav-child-active text-primary' : ' text-secondary hover:bg-bg-secondary hover:text-primary' }} flex items-center gap-2.5 py-1.5 pl-10 pr-3 nav-text-xs rounded-md transition-colors"
                                   {{ $item['active'] ? 'aria-current="page"' : '' }}>
                                    <span class="flex items-center justify-center flex-shrink-0">
                                        <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="h-4 w-4" />
                                    </span>
                                    <span :class="railOpen ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Demo Components --}}
            @if (request()->routeIs('demo.*'))
                <a href="{{ route('demo.inventory-components') }}"
                   x-show="!search || sectionMatch('demo', 'Components Demo')"
                   :class="railOpen ? 'lg:hidden' : ''"
                   class="nav-item nav-link {{ request()->routeIs('demo.inventory-components') ? 'nav-item-active nav-link-active bg-primary text-primary-contrast' : 'text-secondary hover:bg-bg-secondary hover:text-primary' }} flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors"
                   {{ request()->routeIs('demo.inventory-components') ? 'aria-current="page"' : '' }}>
                    <span class="flex items-center justify-center flex-shrink-0">
                        <x-heroicon-o-sparkles class="h-5 w-5" />
                    </span>
                    <span class="nav-text-sm">Components Demo</span>
                </a>
            @endif
        </nav>

        {{-- Quick Actions --}}
        <div class="mt-4 border-t border-secondary pt-3">
            <div class="px-3 pb-2">
                <div class="nav-text-tiny text-muted uppercase">Quick actions</div>
            </div>
            <div class="space-y-1.5">
                <a href="{{ route('inventory.items.create') }}" class="nav-quick-link flex items-center gap-2 px-3 py-2 nav-text-xs text-primary">
                    <x-heroicon-o-plus class="h-3.5 w-3.5 flex-shrink-0 text-muted" />
                    New Item
                </a>
                <a href="{{ route('accounting.vouchers.sales.create') }}" class="nav-quick-link flex items-center gap-2 px-3 py-2 nav-text-xs text-primary">
                    <x-heroicon-o-receipt-percent class="h-3.5 w-3.5 flex-shrink-0 text-muted" />
                    Sales Voucher
                </a>
                <a href="{{ route('payroll.processing') }}" class="nav-quick-link flex items-center gap-2 px-3 py-2 nav-text-xs text-primary">
                    <x-heroicon-o-cog-6-tooth class="h-3.5 w-3.5 flex-shrink-0 text-muted" />
                    Payroll Processing
                </a>
            </div>
        </div>

        {{-- Footer: active organization --}}
        <div class="mt-4 border-t border-secondary px-3 pt-3">
            <p class="nav-text-sm font-medium text-primary truncate">
                {{ auth()->user()?->currentOrganization?->name ?? 'Wittness Tech' }}
            </p>
            <div class="nav-text-tiny text-muted mt-0.5">Wittness Tech ERP</div>
        </div>
    </div>
</div>