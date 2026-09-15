<div class="relative overflow-hidden">
    {{-- Decorative background glows --}}
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="absolute -top-24 left-1/2 h-[34rem] w-[34rem] -translate-x-1/2 rounded-full blur-3xl"
            style="background: radial-gradient(circle, rgba(var(--color-primary-rgb), 0.14) 0%, transparent 70%);">
        </div>
        <div class="absolute top-32 -left-24 h-72 w-72 rounded-full blur-3xl"
            style="background: radial-gradient(circle, rgba(var(--color-primary-rgb), 0.1) 0%, transparent 70%);">
        </div>
        <div class="absolute top-40 -right-24 h-72 w-72 rounded-full blur-3xl"
            style="background: radial-gradient(circle, rgba(var(--color-primary-rgb), 0.1) 0%, transparent 70%);">
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 pt-20 pb-16 sm:px-6 sm:pt-28 sm:pb-20 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-secondary bg-surface px-4 py-1.5 text-sm font-medium text-secondary shadow-sm">
                <span class="inline-block size-2 rounded-full bg-success"></span>
                The all-in-one ERP trusted by growing teams
            </span>

            <h1
                class="mt-6 text-4xl font-extrabold tracking-tight text-balance sm:text-5xl lg:text-6xl">
                Run your entire business from
                <span
                    style="background-image: linear-gradient(to right, var(--color-primary), var(--color-primary-light)); -webkit-background-clip: text; background-clip: text; color: transparent;">one
                    platform</span>
            </h1>

            <p class="mt-6 text-lg leading-8 text-secondary sm:text-xl">
                Human resources, payroll, finance, inventory, and organization
                management — unified, secure, and built for multi-tenant businesses.
            </p>

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <x-button.primary href="{{ route('register') }}" size="xl" class="w-full sm:w-auto">
                    Get started free
                </x-button.primary>
                <x-button.secondary href="#features" size="xl" class="w-full sm:w-auto">
                    Explore features
                </x-button.secondary>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-secondary">
                <span class="inline-flex items-center gap-2">
                    <svg class="size-4 text-success" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    14-day free trial
                </span>
                <span class="inline-flex items-center gap-2">
                    <svg class="size-4 text-success" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    No credit card required
                </span>
                <span class="inline-flex items-center gap-2">
                    <svg class="size-4 text-success" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    Cancel anytime
                </span>
            </div>
        </div>

        {{-- Product preview --}}
        <div class="relative mx-auto mt-16 max-w-5xl">
            <div class="absolute -inset-x-8 -top-6 -bottom-6 -z-10 rounded-3xl"
                style="background: linear-gradient(180deg, rgba(var(--color-primary-rgb), 0.12), rgba(var(--color-primary-rgb), 0.02));">
            </div>

            <div class="surface overflow-hidden rounded-2xl shadow-xl sm:rounded-3xl">
                {{-- Fake window chrome --}}
                <div class="flex items-center gap-2 border-b border-secondary bg-bg-secondary px-5 py-3">
                    <span class="size-3 rounded-full bg-error/70"></span>
                    <span class="size-3 rounded-full bg-warning/70"></span>
                    <span class="size-3 rounded-full bg-success/70"></span>
                    <span class="ml-4 hidden flex-1 rounded-md bg-surface px-3 py-1.5 text-xs text-muted sm:block">
                        hrm.wittness.tech/dashboard
                    </span>
                </div>

                <div class="grid gap-5 p-5 sm:p-8 lg:grid-cols-3">
                    {{-- Stat tiles --}}
                    <div class="grid gap-5 sm:grid-cols-2 lg:col-span-2 lg:grid-cols-2">
                        <div class="rounded-xl border border-secondary bg-bg-secondary p-5">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-secondary">Total employees</p>
                                <span class="inline-flex items-center rounded-full bg-success/10 px-2 py-0.5 text-xs font-semibold text-success">+4.2%</span>
                            </div>
                            <p class="mt-3 text-3xl font-bold">1,284</p>
                            <div class="mt-4 flex h-10 items-end gap-1.5" aria-hidden="true">
                                <div class="h-4 w-full rounded-sm bg-primary/20"></div>
                                <div class="h-6 w-full rounded-sm bg-primary/30"></div>
                                <div class="h-5 w-full rounded-sm bg-primary/25"></div>
                                <div class="h-8 w-full rounded-sm bg-primary/40"></div>
                                <div class="h-7 w-full rounded-sm bg-primary/35"></div>
                                <div class="h-9 w-full rounded-sm bg-primary/50"></div>
                                <div class="h-10 w-full rounded-sm bg-primary/60"></div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-secondary bg-bg-secondary p-5">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-secondary">Pending payroll</p>
                                <span class="inline-flex items-center rounded-full bg-warning/15 px-2 py-0.5 text-xs font-semibold text-warning">Due Fri</span>
                            </div>
                            <p class="mt-3 text-3xl font-bold">$482,150</p>
                            <div class="mt-4 flex h-10 items-end gap-1.5" aria-hidden="true">
                                <div class="h-7 w-full rounded-sm bg-primary/30"></div>
                                <div class="h-5 w-full rounded-sm bg-primary/20"></div>
                                <div class="h-8 w-full rounded-sm bg-primary/40"></div>
                                <div class="h-6 w-full rounded-sm bg-primary/25"></div>
                                <div class="h-10 w-full rounded-sm bg-primary/60"></div>
                                <div class="h-9 w-full rounded-sm bg-primary/50"></div>
                                <div class="h-8 w-full rounded-sm bg-primary/40"></div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-secondary bg-bg-secondary p-5">
                            <p class="text-sm font-medium text-secondary">Low stock alerts</p>
                            <p class="mt-3 text-3xl font-bold">12</p>
                            <div class="mt-4 flex items-center gap-2 text-xs font-medium text-warning">
                                <span class="inline-flex items-center gap-1 rounded-full bg-warning/15 px-2.5 py-1">
                                    <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625l6.28-10.875zM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    4 need reorder today
                                </span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-secondary bg-bg-secondary p-5">
                            <p class="text-sm font-medium text-secondary">On-time attendance</p>
                            <div class="mt-3 flex items-center justify-between">
                                <p class="text-3xl font-bold">96.8%</p>
                                <svg class="size-8 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 3v18h18" />
                                    <path d="m7 14 4-4 4 4 5-6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Recent activity list --}}
                    <div class="rounded-xl border border-secondary bg-bg-secondary p-5">
                        <p class="text-sm font-medium text-secondary">Recent activity</p>
                        <ul class="mt-4 space-y-4 text-sm">
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-accent">AM</span>
                                <div>
                                    <p class="font-medium text-primary">4 new employees onboarded</p>
                                    <p class="text-xs text-muted">2 hours ago · HR Team</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-accent">SK</span>
                                <div>
                                    <p class="font-medium text-primary">Payroll run approved</p>
                                    <p class="text-xs text-muted">Yesterday · Finance</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-accent">TW</span>
                                <div>
                                    <p class="font-medium text-primary">Invoice #2048 paid</p>
                                    <p class="text-xs text-muted">Yesterday · Accounting</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>