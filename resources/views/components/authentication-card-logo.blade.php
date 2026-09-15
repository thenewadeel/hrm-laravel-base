{{-- Brand mark signed off for every guest-facing auth page --}}
<a href="{{ url('/') }}" class="group inline-flex items-center">
    <img src="{{ asset('images/logos/wittness/wittness-dark.png') }}" alt="Wittness Tech"
        class="h-12 w-auto transition-opacity duration-200 group-hover:opacity-90 dark:hidden">
    <img src="{{ asset('images/logos/wittness/wittness-light.png') }}" alt="Wittness Tech"
        class="hidden h-12 w-auto transition-opacity duration-200 group-hover:opacity-90 dark:block">
</a>