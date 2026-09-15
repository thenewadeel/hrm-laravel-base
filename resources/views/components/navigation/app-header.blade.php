@props([])

<header class="surface border-b border-secondary sticky top-0 z-30"
        x-data="{
            searchOpen: false,
            search: '',
            notifOpen: false,
            userOpen: false,
            links: [
                { label: 'Dashboard', href: '{{ route('dashboard') }}' },
                { label: 'Inventory Items', href: '{{ route('inventory.items.index') }}' },
                { label: 'Chart of Accounts', href: '{{ route('accounts.index') }}' },
                { label: 'Employees', href: '{{ route('hr.employees.index') }}' },
                { label: 'Payroll', href: '{{ route('payroll.dashboard') }}' },
                { label: 'Members', href: '{{ route('members.index') }}' },
                { label: 'Organization', href: '{{ route('organization.dashboard') }}' },
                { label: 'Reports', href: '{{ route('inventory.reports.stock-levels') }}' }
            ],
            get filtered() {
                if (!this.search) return this.links;
                const q = this.search.toLowerCase();
                return this.links.filter(l => l.label.toLowerCase().includes(q));
            }
        }"
        @keydown.escape.window="searchOpen = false; notifOpen = false; userOpen = false"
        role="banner">

    <div class="flex items-center justify-between h-16 px-4 sm:px-6">
        {{-- Left: mobile hamburger --}}
        <div class="flex items-center">
            <button @click="$dispatch('toggle-nav')"
                    class="inline-flex items-center justify-center p-2 rounded-md text-secondary hover:text-primary hover:bg-secondary transition-colors md:hidden"
                    aria-controls="sidebar"
                    aria-label="Toggle navigation">
                <span class="sr-only">Toggle navigation</span>
                <x-heroicon-o-bars-3 class="h-6 w-6" />
            </button>
        </div>

        {{-- Right: desktop actions --}}
        <div class="hidden md:flex items-center space-x-2">

            {{-- Search popover --}}
            <div class="relative">
                <button @click="searchOpen = !searchOpen; notifOpen = false; userOpen = false"
                        class="p-2 rounded-md text-secondary hover:text-primary hover:bg-secondary transition-colors"
                        aria-label="Search navigation"
                        :aria-expanded="searchOpen">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                </button>

                <div x-show="searchOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="searchOpen = false"
                     class="absolute right-0 mt-2 w-72 surface border border-secondary rounded-lg shadow-lg z-50">
                    <div class="p-3">
                        <input type="text"
                               x-ref="searchInput"
                               x-model="search"
                               placeholder="Search..."
                               class="w-full px-3 py-2 text-sm bg-bg-tertiary border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary text-primary placeholder-muted">
                    </div>
                    <div class="border-t border-secondary max-h-64 overflow-y-auto">
                        <template x-if="filtered.length === 0">
                            <p class="px-4 py-3 text-sm text-muted">No results found</p>
                        </template>
                        <template x-for="link in filtered" :key="link.href">
                            <a :href="link.href"
                               class="block px-4 py-2 text-sm text-primary hover:bg-secondary transition-colors"
                               x-text="link.label"></a>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="relative">
                <button @click="notifOpen = !notifOpen; searchOpen = false; userOpen = false"
                        class="p-2 rounded-md text-secondary hover:text-primary hover:bg-secondary transition-colors"
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
                        <h3 class="text-sm font-semibold text-primary">Notifications</h3>
                    </div>
                    <div class="px-4 py-8 text-center">
                        <p class="text-sm text-muted">No new notifications</p>
                    </div>
                </div>
            </div>

            {{-- Language switcher --}}
            <x-navigation.language-switcher />

            {{-- Theme toggle --}}
            <x-navigation.theme-toggle />

            {{-- User menu --}}
            <div class="relative">
                <button @click="userOpen = !userOpen; searchOpen = false; notifOpen = false"
                        class="flex items-center space-x-2 p-1.5 rounded-md text-secondary hover:text-primary hover:bg-secondary transition-colors"
                        :aria-expanded="userOpen"
                        aria-label="User menu">
                    @auth
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <img class="avatar h-8 w-8 rounded-full object-cover"
                                 src="{{ Auth::user()->profile_photo_url }}"
                                 alt="{{ Auth::user()->name }}">
                        @else
                            <span class="avatar inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary text-primary-contrast text-sm font-medium">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </span>
                        @endif
                        <span class="hidden lg:inline text-sm font-medium text-primary">{{ Auth::user()->name }}</span>
                        <x-heroicon-o-chevron-down class="w-4 h-4 text-muted" />
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
                     class="absolute right-0 mt-2 w-56 surface border border-secondary rounded-lg shadow-lg z-50">
                    @auth
                        <div class="px-4 py-3 border-b border-secondary">
                            <p class="text-sm font-medium text-primary">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-muted truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <div class="py-1">
                            <a href="{{ route('profile.show') }}"
                               class="block px-4 py-2 text-sm text-primary hover:bg-secondary transition-colors">
                                Profile
                            </a>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <a href="{{ route('api-tokens.index') }}"
                                   class="block px-4 py-2 text-sm text-primary hover:bg-secondary transition-colors">
                                    API Tokens
                                </a>
                            @endif
                        </div>

                        <div class="border-t border-secondary py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-sm text-primary hover:bg-secondary transition-colors">
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
