<div class="relative flex min-h-screen flex-col items-center justify-center bg-bg-primary px-4 py-12 sm:px-6">
    {{-- Decorative background accents, consistent with the landing page --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full blur-3xl"
            style="background: radial-gradient(circle, rgba(var(--color-primary-rgb), 0.12) 0%, transparent 70%);"></div>
        <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full blur-3xl"
            style="background: radial-gradient(circle, rgba(var(--color-primary-rgb), 0.08) 0%, transparent 70%);"></div>
        <div class="absolute -bottom-24 -right-24 h-80 w-80 rounded-full blur-3xl"
            style="background: radial-gradient(circle, rgba(var(--color-primary-rgb), 0.08) 0%, transparent 70%);"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="mb-8 flex justify-center">
            {{ $logo }}
        </div>

        <div class="surface">
            <div class="px-6 py-8 sm:px-8 sm:py-10">
                {{ $slot }}
            </div>
        </div>

        <p class="mt-6 text-center text-xs font-medium text-muted">
            {{ __('Powered by the HRM Enterprise ERP suite') }}
        </p>
    </div>
</div>