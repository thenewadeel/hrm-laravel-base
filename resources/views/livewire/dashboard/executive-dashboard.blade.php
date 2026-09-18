<div class="py-6">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        {{-- Command center hero --}}
        <section class="relative overflow-hidden rounded-2xl border border-secondary surface"
                 aria-label="Command center">
            <canvas x-ref="particleCanvas"
                    x-data="particleField"
                    class="absolute inset-0 h-full w-full opacity-[0.55] pointer-events-none"
                    aria-hidden="true"></canvas>

            <div class="relative z-10 px-6 py-8 sm:px-8 lg:px-10" x-data="{ customizeOpen: false }">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-secondary bg-bg-secondary/70 px-3 py-1 text-xs font-medium text-secondary">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-60" style="background: var(--color-success)"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full" style="background: var(--color-success)"></span>
                            </span>
                            Eagle Eye • Command Center
                        </div>
                        <h1 class="mt-4 text-3xl font-semibold tracking-tight text-primary sm:text-4xl">
                            {{ $organizationName }}
                        </h1>
                        <p class="mt-2 max-w-2xl text-secondary">
                            Live cross-module intelligence · <span x-text="liveDate" x-data="liveClock"></span>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="$wire.refresh()"
                                class="nav-icon-btn h-10 w-10 inline-flex items-center justify-center rounded-lg text-secondary hover:text-primary hover:bg-bg-secondary"
                                aria-label="Refresh dashboard data">
                            <x-heroicon-o-arrow-path :class="{'animate-spin': $wire.get('saved')}" class="h-5 w-5" />
                        </button>
                        <button type="button"
                                @click="customizeOpen = !customizeOpen"
                                :aria-expanded="customizeOpen"
                                class="nav-icon-btn h-10 w-10 inline-flex items-center justify-center rounded-lg text-secondary hover:text-primary hover:bg-bg-secondary"
                                aria-label="Customize dashboard widgets">
                            <x-heroicon-o-squares-plus class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                {{-- Customize panel --}}
                <div x-show="customizeOpen" x-cloak x-collapse.origin.top class="mt-6">
                    <div class="rounded-xl border border-secondary bg-bg-secondary/70 p-4">
                        <h2 class="text-sm font-semibold text-primary">Toggle widgets</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($widgets as $key => $widget)
                                <button type="button"
                                        @click="$wire.toggleWidget('{{ $key }}')"
                                        class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm transition-colors"
                                        :class="$wire.layout.includes('{{ $key }}') ? 'border-primary bg-primary text-primary-contrast' : 'border-secondary text-secondary hover:text-primary'">
                                    <x-heroicon-o-squares-plus class="h-4 w-4" />
                                    {{ $widget['title'] }}
                                </button>
                            @endforeach
                        </div>
                        <p x-show="$wire.saved" x-cloak class="mt-3 text-xs text-success">Layout saved to your account.</p>
                    </div>
                </div>
            </div>

            {{-- KPI strip --}}
            <div class="relative z-10 border-t border-secondary bg-bg-secondary/50 px-6 py-5 sm:px-8 lg:px-10">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-7" data-executive-kpis>
                    @foreach ($kpis as $kpi)
                        @include('livewire.dashboard.partials.kpi-card', ['kpi' => $kpi])
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Draggable widget grid --}}
        <section class="dashboard-grid"
                 data-widget-grid
                 x-data="widgetGrid($wire)"
                 aria-label="Dashboard widgets">
            @foreach ($layout as $key)
                @php $widget = $widgets[$key] ?? null; @endphp
                @if ($widget)
                    @include('livewire.dashboard.partials.widget-card', ['key' => $key, 'widget' => $widget])
                @endif
            @endforeach
        </section>
    </div>
</div>