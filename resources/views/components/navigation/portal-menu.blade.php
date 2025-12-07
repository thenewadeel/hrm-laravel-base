{{-- Portal Navigation - Employee Portal --}}
@if(auth()->check() && auth()->user()->hasRole('employee'))
    <x-navigation.section title="Employee Portal" icon="👤">
        <x-navigation.mobile-link 
            href="{{ route('portal.employee.dashboard') }}" 
            :active="request()->routeIs('portal.employee.*')"
            icon="🏠"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Dashboard
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('portal.employee.attendance') }}" 
            :active="request()->routeIs('portal.employee.attendance')"
            icon="⏱️"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            My Attendance
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('portal.employee.leave') }}" 
            :active="request()->routeIs('portal.employee.leave')"
            icon="📅"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Leave Requests
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('portal.employee.payslips') }}" 
            :active="request()->routeIs('portal.employee.payslips')"
            icon="💰"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Pay Slips
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('portal.employee.setup') }}" 
            :active="request()->routeIs('portal.employee.setup')"
            icon="⚙️"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Account Setup
        </x-navigation.mobile-link>
    </x-navigation.section>
@endif

{{-- Portal Navigation - Manager Portal --}}
@if(auth()->check() && auth()->user()->hasRole('manager'))
    <x-navigation.section title="Manager Portal" icon="👨‍💼">
        <x-navigation.mobile-link 
            href="{{ route('portal.manager.dashboard') }}" 
            :active="request()->routeIs('portal.manager.*')"
            icon="📊"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Dashboard
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('portal.manager.team-attendance') }}" 
            :active="request()->routeIs('portal.manager.team-attendance')"
            icon="👥"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Team Attendance
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('portal.manager.reports') }}" 
            :active="request()->routeIs('portal.manager.reports')"
            icon="📈"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Reports
        </x-navigation.mobile-link>
    </x-navigation.section>
@endif