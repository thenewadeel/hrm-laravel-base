<x-guest-layout title="Enterprise ERP Platform" description="HRM is an all-in-one ERP uniting HR, payroll, finance, inventory, and organization management in one secure multi-tenant workspace.">

    {{-- Navbar --}}
    <header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-secondary bg-bg-primary/80 backdrop-blur-md">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Main">
            <a href="{{ url('/') }}" class="flex items-center">
                <img src="{{ asset('images/logos/wittness/wittness-dark.png') }}" alt="Wittness Tech"
                    class="h-8 w-auto transition-opacity duration-200 hover:opacity-90 dark:hidden">
                <img src="{{ asset('images/logos/wittness/wittness-light.png') }}" alt="Wittness Tech"
                    class="hidden h-8 w-auto transition-opacity duration-200 hover:opacity-90 dark:block">
            </a>

            <div class="hidden items-center gap-8 lg:flex">
                <a href="#features" class="text-sm font-medium text-secondary transition-colors hover:text-primary">Features</a>
                <a href="#modules" class="text-sm font-medium text-secondary transition-colors hover:text-primary">Modules</a>
                <a href="#pricing" class="text-sm font-medium text-secondary transition-colors hover:text-primary">Pricing</a>
                <a href="#testimonials" class="text-sm font-medium text-secondary transition-colors hover:text-primary">Customers</a>
            </div>

            <div class="hidden items-center gap-3 lg:flex">
                <button type="button" onclick="document.dispatchEvent(new CustomEvent('toggle-theme'))"
                    class="flex size-9 items-center justify-center rounded-lg border border-secondary text-secondary transition-colors hover:bg-secondary"
                    aria-label="Toggle dark mode">
                    <x-heroicon-o-sun class="size-4" />
                </button>
                <a href="{{ route('login') }}"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-primary transition-colors hover:bg-secondary">Log in</a>
                <x-button.primary href="{{ route('register') }}">
                    Get started
                </x-button.primary>
            </div>

            {{-- Mobile menu --}}
            <div class="flex items-center gap-2 lg:hidden">
                <button type="button" onclick="document.dispatchEvent(new CustomEvent('toggle-theme'))"
                    class="flex size-9 items-center justify-center rounded-lg border border-secondary text-secondary transition-colors hover:bg-secondary"
                    aria-label="Toggle dark mode">
                    <x-heroicon-o-sun class="size-4" />
                </button>
                <button type="button" @click="mobileOpen = !mobileOpen"
                    class="flex size-9 items-center justify-center rounded-lg border border-secondary text-secondary transition-colors hover:bg-secondary"
                    aria-label="Toggle navigation menu">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path x-show="!mobileOpen" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileOpen" d="m6 6 12 12M6 18 18 6" />
                    </svg>
                </button>
            </div>
        </nav>

        {{-- Mobile panel --}}
        <div x-cloak x-show="mobileOpen" @keydown.escape.window="mobileOpen = false" class="lg:hidden" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2">
            <div class="space-y-1 border-t border-secondary bg-bg-primary px-4 pt-2 pb-4 sm:px-6">
                <a href="#features" @click="mobileOpen = false"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-secondary">Features</a>
                <a href="#modules" @click="mobileOpen = false"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-secondary">Modules</a>
                <a href="#pricing" @click="mobileOpen = false"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-secondary">Pricing</a>
                <a href="#testimonials" @click="mobileOpen = false"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-secondary hover:bg-secondary">Customers</a>
                <div class="mt-3 flex flex-col gap-2 border-t border-secondary pt-3">
                    <a href="{{ route('login') }}"
                        class="rounded-lg border border-secondary px-4 py-2.5 text-center text-sm font-medium text-primary transition-colors hover:bg-secondary">Log in</a>
                    <x-button.primary href="{{ route('register') }}" class="justify-center">
                        Get started
                    </x-button.primary>
                </div>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <x-welcome />

    {{-- Stats band --}}
    <section class="border-y border-secondary bg-bg-secondary" aria-label="Platform statistics">
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-4 py-12 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div class="text-center">
                <p class="text-3xl font-bold text-accent sm:text-4xl">500+</p>
                <p class="mt-1 text-sm text-secondary">Active organizations</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-accent sm:text-4xl">12k+</p>
                <p class="mt-1 text-sm text-secondary">Employees managed</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-accent sm:text-4xl">99.9%</p>
                <p class="mt-1 text-sm text-secondary">Uptime SLA</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-accent sm:text-4xl">24/7</p>
                <p class="mt-1 text-sm text-secondary">Expert support</p>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="scroll-mt-20 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-accent">Everything you need</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">One platform for the entire business</h2>
                <p class="mt-4 text-lg text-secondary">
                    Stop juggling disconnected tools. HRM brings every department together with a single source of truth.
                </p>
            </div>

            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Feature 1 --}}
                <div class="group rounded-2xl border border-secondary bg-surface p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-accent">
                        <x-heroicon-o-user-group class="size-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold">Employee Management</h3>
                    <p class="mt-2 text-sm leading-6 text-secondary">
                        Manage the full employee lifecycle — onboarding, documents, profiles, and history — in a
                        privacy-first, organized way.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div class="group rounded-2xl border border-secondary bg-surface p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-accent">
                        <x-heroicon-o-minus class="size-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold">Payroll &amp; Compensation</h3>
                    <p class="mt-2 text-sm leading-6 text-secondary">
                        From increments and allowances to loans and advances, run accurate payroll with tax management
                        built in.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div class="group rounded-2xl border border-secondary bg-surface p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-accent">
                        <x-heroicon-o-clock class="size-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold">Time &amp; Attendance</h3>
                    <p class="mt-2 text-sm leading-6 text-secondary">
                        Track shifts, biometric punches, and leave through approval workflows with employee and manager
                        portals.
                    </p>
                </div>

                {{-- Feature 4 --}}
                <div class="group rounded-2xl border border-secondary bg-surface p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-accent">
                        <x-heroicon-o-chart-bar class="size-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold">Financial Accounting</h3>
                    <p class="mt-2 text-sm leading-6 text-secondary">
                        Double-entry bookkeeping, specialized vouchers, bank reconciliation, fixed assets, and financial
                        year management.
                    </p>
                </div>

                {{-- Feature 5 --}}
                <div class="group rounded-2xl border border-secondary bg-surface p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-accent">
                        <x-heroicon-o-square-3-stack-3d class="size-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold">Inventory Management</h3>
                    <p class="mt-2 text-sm leading-6 text-secondary">
                        Multi-store stock, in/out/transfer/adjust transactions, reorder alerts, and FIFO or weighted
                        average costing.
                    </p>
                </div>

                {{-- Feature 6 --}}
                <div class="group rounded-2xl border border-secondary bg-surface p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-accent">
                        <x-heroicon-o-document-text class="size-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold">Organization &amp; Security</h3>
                    <p class="mt-2 text-sm leading-6 text-secondary">
                        Multi-tenant data isolation, hierarchical structures, granular role-based permissions, and
                        invitation-based onboarding.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Module spotlights --}}
    <section id="modules" class="scroll-mt-20 border-y border-secondary bg-bg-secondary py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-24">
                {{-- HR & Payroll --}}
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-accent">Human Resources</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">People operations, without the paperwork</h2>
                        <p class="mt-4 text-lg text-secondary">
                            Give HR, managers, and employees the tools to work together — from hiring to payroll, from
                            leave to performance.
                        </p>
                        <ul class="mt-8 space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-accent">
                                    <x-heroicon-m-check-circle class="size-4" />
                                </span>
                                <p class="text-sm text-secondary"><span class="font-semibold text-primary">Employee lifecycle.</span> Profiles, documents, positions, and full history in one place.</p>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-accent">
                                    <x-heroicon-m-check-circle class="size-4" />
                                </span>
                                <p class="text-sm text-secondary"><span class="font-semibold text-primary">Payroll that pays.</span> Increments, allowances, deductions, loans, advances, and tax management.</p>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-accent">
                                    <x-heroicon-m-check-circle class="size-4" />
                                </span>
                                <p class="text-sm text-secondary"><span class="font-semibold text-primary">Attend &amp; approve.</span> Shifts, biometric capture, leave workflows, and manager approvals.</p>
                            </li>
                        </ul>
                    </div>
                    <div class="relative">
                        <div class="surface rounded-2xl p-6 shadow-lg">
                            <div class="flex items-center justify-between border-b border-secondary pb-4">
                                <p class="text-sm font-semibold">Payroll overview · July</p>
                                <span class="rounded-full bg-success/10 px-3 py-1 text-xs font-semibold text-success">Processed</span>
                            </div>
                            <dl class="mt-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-secondary">Gross payroll</dt>
                                    <dd class="text-sm font-semibold">$482,150.00</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-secondary">Withholdings</dt>
                                    <dd class="text-sm font-semibold">$ 87,240.00</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-secondary">Net payout</dt>
                                    <dd class="text-sm font-semibold text-success">$394,910.00</dd>
                                </div>
                            </dl>
                            <div class="mt-5 border-t border-secondary pt-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-secondary">Leave approvals pending</span>
                                    <span class="font-semibold text-warning">3</span>
                                </div>
                                <div class="mt-3 flex overflow-hidden rounded-full bg-bg-tertiary">
                                    <div class="h-2 w-2/3 rounded-full bg-primary/70"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Finance & Accounting --}}
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div class="relative order-2 lg:order-1">
                        <div class="surface rounded-2xl p-6 shadow-lg">
                            <div class="flex items-center justify-between border-b border-secondary pb-4">
                                <p class="text-sm font-semibold">Financial position</p>
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-accent">FY 2026</span>
                            </div>
                            <div class="mt-5 grid grid-cols-2 gap-4">
                                <div class="rounded-xl bg-bg-secondary p-4">
                                    <p class="text-xs text-secondary">Total assets</p>
                                    <p class="mt-1 text-lg font-bold">$1.24M</p>
                                    <p class="text-xs font-medium text-success">+8.4%</p>
                                </div>
                                <div class="rounded-xl bg-bg-secondary p-4">
                                    <p class="text-xs text-secondary">Net income</p>
                                    <p class="mt-1 text-lg font-bold">$312k</p>
                                    <p class="text-xs font-medium text-success">+12.1%</p>
                                </div>
                                <div class="rounded-xl bg-bg-secondary p-4">
                                    <p class="text-xs text-secondary">Open receivables</p>
                                    <p class="mt-1 text-lg font-bold">$84k</p>
                                    <p class="text-xs font-medium text-warning">6 overdue</p>
                                </div>
                                <div class="rounded-xl bg-bg-secondary p-4">
                                    <p class="text-xs text-secondary">Bank balance</p>
                                    <p class="mt-1 text-lg font-bold">$478k</p>
                                    <p class="text-xs font-medium text-success">Reconciled</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="order-1 lg:order-2">
                        <p class="text-sm font-semibold uppercase tracking-wider text-accent">Finance &amp; Accounting</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Numbers you can trust, at a glance</h2>
                        <p class="mt-4 text-lg text-secondary">
                            A complete chart of accounts, balanced double-entry, and reporting that keeps your books audit-ready.
                        </p>
                        <ul class="mt-8 space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-accent">
                                    <x-heroicon-m-check-circle class="size-4" />
                                </span>
                                <p class="text-sm text-secondary"><span class="font-semibold text-primary">Double-entry always balanced.</span> Journal entries, ledgers, and specialized vouchers.</p>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-accent">
                                    <x-heroicon-m-check-circle class="size-4" />
                                </span>
                                <p class="text-sm text-secondary"><span class="font-semibold text-primary">Bank &amp; cash.</span> Reconciliation, statements, receipts, and payments with full audit trails.</p>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-accent">
                                    <x-heroicon-m-check-circle class="size-4" />
                                </span>
                                <p class="text-sm text-secondary"><span class="font-semibold text-primary">Financial reporting.</span> Balance sheet, income statement, trial balance, and outstanding aging with PDF export.</p>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Inventory & Multi-store --}}
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-accent">Inventory</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Every item, every store, every movement</h2>
                        <p class="mt-4 text-lg text-secondary">
                            Real-time stock across multiple locations with costing that keeps your margins accurate.
                        </p>
                        <ul class="mt-8 space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-accent">
                                    <x-heroicon-m-check-circle class="size-4" />
                                </span>
                                <p class="text-sm text-secondary"><span class="font-semibold text-primary">Multi-store visibility.</span> Track stock levels and value across every location in real time.</p>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-accent">
                                    <x-heroicon-m-check-circle class="size-4" />
                                </span>
                                <p class="text-sm text-secondary"><span class="font-semibold text-primary">Every movement logged.</span> Stock in, out, transfer, and adjustment with full history.</p>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-accent">
                                    <x-heroicon-m-check-circle class="size-4" />
                                </span>
                                <p class="text-sm text-secondary"><span class="font-semibold text-primary">Smart reordering.</span> Automated low-stock alerts and FIFO or weighted-average valuation.</p>
                            </li>
                        </ul>
                    </div>
                    <div class="relative">
                        <div class="surface rounded-2xl p-6 shadow-lg">
                            <div class="flex items-center justify-between border-b border-secondary pb-4">
                                <p class="text-sm font-semibold">Stock levels</p>
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-accent">3 stores</span>
                            </div>
                            <ul class="mt-5 divide-y divide-secondary">
                                <li class="flex items-center justify-between gap-4 py-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold">Wireless Mouse M100</p>
                                        <p class="text-xs text-muted">SKU: WM-100</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-success/10 px-2.5 py-1 text-xs font-semibold text-success">428 in stock</span>
                                </li>
                                <li class="flex items-center justify-between gap-4 py-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold">Ergonomic Chair Pro</p>
                                        <p class="text-xs text-muted">SKU: EC-210</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-warning/15 px-2.5 py-1 text-xs font-semibold text-warning">14 low stock</span>
                                </li>
                                <li class="flex items-center justify-between gap-4 py-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold">24" Monitor 2K</p>
                                        <p class="text-xs text-muted">SKU: MN-24</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-error/10 px-2.5 py-1 text-xs font-semibold text-error">0 out of stock</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-accent">Get started in minutes</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Up and running in 3 simple steps</h2>
            </div>
            <div class="mt-16 grid gap-10 md:grid-cols-3">
                <div class="relative">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-primary text-inverse shadow-lg">
                        <span class="text-lg font-bold">1</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold">Create your workspace</h3>
                    <p class="mt-2 text-sm leading-6 text-secondary">
                        Set up your organization and structure in minutes — no technical setup required.
                    </p>
                </div>
                <div class="relative">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-primary text-inverse shadow-lg">
                        <span class="text-lg font-bold">2</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold">Invite your people</h3>
                    <p class="mt-2 text-sm leading-6 text-secondary">
                        Add members with role-based permissions and organize them into your company's hierarchy.
                    </p>
                </div>
                <div class="relative">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-primary text-inverse shadow-lg">
                        <span class="text-lg font-bold">3</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold">Run your business</h3>
                    <p class="mt-2 text-sm leading-6 text-secondary">
                        Manage HR, payroll, finance, and inventory from one unified, real-time dashboard.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section id="testimonials" class="scroll-mt-20 border-y border-secondary bg-bg-secondary py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-accent">Loved by teams</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Trusted by operations leaders everywhere</h2>
            </div>
            <div class="mt-16 grid gap-6 md:grid-cols-3">
                <figure class="flex flex-col rounded-2xl border border-secondary bg-surface p-6 shadow-sm">
                    <div class="flex gap-1 text-warning" aria-label="5 out of 5 stars">
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                    </div>
                    <blockquote class="mt-4 flex-1 text-sm leading-6 text-secondary">
                        "We replaced four separate tools with HRM. Payroll went from a weekend of spreadsheets to a
                        two-hour run, and finance finally has one source of truth."
                    </blockquote>
                    <figcaption class="mt-6 flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-full bg-primary/10 font-bold text-accent">AS</span>
                        <div>
                            <p class="text-sm font-semibold">Amara Shah</p>
                            <p class="text-xs text-muted">COO, Vertex Logistics</p>
                        </div>
                    </figcaption>
                </figure>

                <figure class="flex flex-col rounded-2xl border border-secondary bg-surface p-6 shadow-sm">
                    <div class="flex gap-1 text-warning" aria-label="5 out of 5 stars">
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                    </div>
                    <blockquote class="mt-4 flex-1 text-sm leading-6 text-secondary">
                        "The multi-tenancy is exactly what we needed as an agency running multiple clients. Data stays
                        isolated, reporting stays clean, and onboarding new clients is instant."
                    </blockquote>
                    <figcaption class="mt-6 flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-full bg-primary/10 font-bold text-accent">DK</span>
                        <div>
                            <p class="text-sm font-semibold">David Kim</p>
                            <p class="text-xs text-muted">Managing Partner, Northstar Agency</p>
                        </div>
                    </figcaption>
                </figure>

                <figure class="flex flex-col rounded-2xl border border-secondary bg-surface p-6 shadow-sm">
                    <div class="flex gap-1 text-warning" aria-label="5 out of 5 stars">
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                        <x-heroicon-m-sparkles class="size-4" />
                    </div>
                    <blockquote class="mt-4 flex-1 text-sm leading-6 text-secondary">
                        "Our auditors love the trail. Every voucher, every movement, every approval has an owner and a
                        timestamp. Year-end close used to take weeks — now it's a day."
                    </blockquote>
                    <figcaption class="mt-6 flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-full bg-primary/10 font-bold text-accent">LR</span>
                        <div>
                            <p class="text-sm font-semibold">Lucia Rivera</p>
                            <p class="text-xs text-muted">CFO, Brightline Retail</p>
                        </div>
                    </figcaption>
                </figure>
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section id="pricing" class="scroll-mt-20 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-accent">Pricing</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Simple, transparent pricing</h2>
                <p class="mt-4 text-lg text-secondary">
                    Start free and scale as you grow. Every plan includes core HR, payroll, finance, and inventory
                    modules.
                </p>
            </div>

            <div class="mt-16 grid gap-8 lg:grid-cols-3">
                {{-- Starter --}}
                <div class="flex flex-col rounded-2xl border border-secondary bg-surface p-8 shadow-sm">
                    <h3 class="text-lg font-semibold">Starter</h3>
                    <p class="mt-2 text-sm text-secondary">For small teams just getting organized.</p>
                    <p class="mt-6 flex items-baseline gap-1">
                        <span class="text-4xl font-bold">$0</span>
                        <span class="text-sm text-muted">/ forever</span>
                    </p>
                    <ul class="mt-6 flex-1 space-y-3 text-sm text-secondary">
                        <li class="flex items-center gap-2"><span class="text-success">✓</span> Up to 10 employees</li>
                        <li class="flex items-center gap-2"><span class="text-success">✓</span> Core HR &amp; organization</li>
                        <li class="flex items-center gap-2"><span class="text-success">✓</span> Payroll &amp; attendance</li>
                        <li class="flex items-center gap-2"><span class="text-success">✓</span> Email support</li>
                    </ul>
                    <x-button.secondary href="{{ route('register') }}" class="mt-8 w-full justify-center">
                        Start for free
                    </x-button.secondary>
                </div>

                {{-- Growth --}}
                <div class="relative flex flex-col rounded-2xl bg-primary p-8 text-inverse shadow-xl lg:-my-4 lg:py-12">
                    <span class="absolute -top-3 right-6 rounded-full bg-text-inverse px-3 py-1 text-xs font-semibold uppercase tracking-wide text-primary shadow-md">Most popular</span>
                    <h3 class="text-lg font-semibold">Growth</h3>
                    <p class="mt-2 text-sm opacity-80">For growing companies that need it all.</p>
                    <p class="mt-6 flex items-baseline gap-1">
                        <span class="text-4xl font-bold">$29</span>
                        <span class="text-sm opacity-80">/ user / month</span>
                    </p>
                    <ul class="mt-6 flex-1 space-y-3 text-sm">
                        <li class="flex items-center gap-2"><span class="font-bold">✓</span> Everything in Starter</li>
                        <li class="flex items-center gap-2"><span class="font-bold">✓</span> Unlimited employees</li>
                        <li class="flex items-center gap-2"><span class="font-bold">✓</span> Multi-store inventory</li>
                        <li class="flex items-center gap-2"><span class="font-bold">✓</span> Advanced accounting &amp; reporting</li>
                        <li class="flex items-center gap-2"><span class="font-bold">✓</span> Priority support</li>
                    </ul>
                    <a href="{{ route('register') }}"
                        class="mt-8 inline-flex w-full items-center justify-center rounded-md bg-text-inverse px-6 py-3 text-base font-medium text-primary transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-primary focus:ring-text-inverse">
                        Get started
                    </a>
                </div>

                {{-- Enterprise --}}
                <div class="flex flex-col rounded-2xl border border-secondary bg-surface p-8 shadow-sm">
                    <h3 class="text-lg font-semibold">Enterprise</h3>
                    <p class="mt-2 text-sm text-secondary">For organizations with complex needs.</p>
                    <p class="mt-6 flex items-baseline gap-1">
                        <span class="text-4xl font-bold">Custom</span>
                    </p>
                    <ul class="mt-6 flex-1 space-y-3 text-sm text-secondary">
                        <li class="flex items-center gap-2"><span class="text-success">✓</span> Everything in Growth</li>
                        <li class="flex items-center gap-2"><span class="text-success">✓</span> Single sign-on (SSO)</li>
                        <li class="flex items-center gap-2"><span class="text-success">✓</span> Dedicated account manager</li>
                        <li class="flex items-center gap-2"><span class="text-success">✓</span> Custom integrations &amp; SLA</li>
                    </ul>
                    <x-button.secondary href="#contact" class="mt-8 w-full justify-center">
                        Contact sales
                    </x-button.secondary>
                </div>
            </div>

            <p class="mt-10 text-center text-sm text-muted">
                All plans include bank-grade security, 99.9% uptime SLA, and automatic backups.
            </p>
        </div>
    </section>

    {{-- CTA --}}
    <section id="contact" class="scroll-mt-20 pb-20 sm:pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="surface relative overflow-hidden rounded-3xl px-6 py-16 text-center sm:px-16 sm:py-20">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute -top-24 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full blur-3xl"
                        style="background: radial-gradient(circle, rgba(var(--color-primary-rgb), 0.14) 0%, transparent 70%);">
                    </div>
                </div>
                <div class="relative">
                    <h2 class="mx-auto max-w-2xl text-3xl font-bold tracking-tight text-balance sm:text-4xl">
                        Ready to run your business from one platform?
                    </h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-secondary">
                        Join hundreds of teams that manage their people, money, and inventory without the chaos.
                    </p>
                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <x-button.primary href="{{ route('register') }}" size="xl" class="w-full sm:w-auto">
                            Start your free trial
                        </x-button.primary>
                        <x-button.secondary href="#features" size="xl" class="w-full sm:w-auto">
                            Talk to an expert
                        </x-button.secondary>
                    </div>
                    <p class="mt-6 text-sm text-muted">Free 14-day trial · No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-secondary bg-bg-secondary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <a href="{{ url('/') }}" class="flex items-center">
                        <img src="{{ asset('images/logos/wittness/wittness-dark.png') }}" alt="Wittness Tech"
                            class="h-8 w-auto transition-opacity duration-200 hover:opacity-90 dark:hidden">
                        <img src="{{ asset('images/logos/wittness/wittness-light.png') }}" alt="Wittness Tech"
                            class="hidden h-8 w-auto transition-opacity duration-200 hover:opacity-90 dark:block">
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-secondary">
                        An all-in-one ERP platform for human resources, payroll, finance, inventory, and organization
                        management.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-primary">Product</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="#features" class="text-secondary transition-colors hover:text-primary">Features</a></li>
                        <li><a href="#modules" class="text-secondary transition-colors hover:text-primary">Modules</a></li>
                        <li><a href="#pricing" class="text-secondary transition-colors hover:text-primary">Pricing</a></li>
                        <li><a href="#" class="text-secondary transition-colors hover:text-primary">Changelog</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-primary">Company</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="#testimonials" class="text-secondary transition-colors hover:text-primary">Customers</a></li>
                        <li><a href="#" class="text-secondary transition-colors hover:text-primary">About us</a></li>
                        <li><a href="#" class="text-secondary transition-colors hover:text-primary">Careers</a></li>
                        <li><a href="#contact" class="text-secondary transition-colors hover:text-primary">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-primary">Resources</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="#" class="text-secondary transition-colors hover:text-primary">Documentation</a></li>
                        <li><a href="#" class="text-secondary transition-colors hover:text-primary">API reference</a></li>
                        <li><a href="#" class="text-secondary transition-colors hover:text-primary">Help center</a></li>
                        <li><a href="{{ route('register') }}" class="text-secondary transition-colors hover:text-primary">Get started</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-secondary pt-8 sm:flex-row">
                <p class="text-sm text-muted">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="flex items-center gap-6 text-sm text-muted">
                    <a href="#" class="transition-colors hover:text-primary">Privacy</a>
                    <a href="#" class="transition-colors hover:text-primary">Terms</a>
                    <a href="#" class="transition-colors hover:text-primary">Security</a>
                </div>
            </div>
        </div>
    </footer>

</x-guest-layout>