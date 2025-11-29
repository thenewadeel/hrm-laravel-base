{{-- Portal Navigation - Employee Portal --}}
@if(auth()->check() && auth()->user()->hasRole('employee'))
    <x-navigation.link 
        href="{{ route('portal.employee.dashboard') }}" 
        :active="request()->routeIs('portal.employee.dashboard')"
        icon="👤"
        badge="Portal"
    >
        Employee Portal
    </x-navigation.link>
@endif

{{-- Portal Navigation - Manager Portal --}}
@if(auth()->check() && auth()->user()->hasRole('manager'))
    <x-navigation.link 
        href="{{ route('portal.manager.dashboard') }}" 
        :active="request()->routeIs('portal.manager.*')"
        icon="👨‍💼"
        badge="Portal"
    >
        Manager Portal
    </x-navigation.link>
@endif