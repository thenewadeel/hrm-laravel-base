{{-- Portal Navigation - Employee Portal --}}
@if (auth()->check() && auth()->user()->hasRole('employee'))
    <x-navigation.dropdown align="left" width="56">
        <x-slot name="trigger">
            <x-navigation.link href="#" icon="👤" :active="request()->routeIs('portal.employee.*')">
                Employee Portal
                <x-heroicon-m-chevron-down class="ml-1 -mr-0.5 h-4 w-4 flex-shrink-0" />
            </x-navigation.link>
        </x-slot>

        <x-navigation.dropdown-link href="{{ route('portal.employee.dashboard') }}" icon="🏠">
                Dashboard
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('portal.employee.attendance') }}" icon="⏱️">
                My Attendance
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('portal.employee.leave') }}" icon="📅">
                Leave Requests
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('portal.employee.payslips') }}" icon="💰">
                Pay Slips
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('portal.employee.setup') }}" icon="⚙️">
                Account Setup
            </x-navigation.dropdown-link>
    </x-navigation.dropdown>
@endif

{{-- Portal Navigation - Manager Portal --}}
@if (auth()->check() && auth()->user()->hasRole('manager'))
    <x-navigation.dropdown align="left" width="56">
        <x-slot name="trigger">
            <x-navigation.link href="#" icon="👨‍💼" :active="request()->routeIs('portal.manager.*')">
                Manager Portal
                <x-heroicon-m-chevron-down class="ml-1 -mr-0.5 h-4 w-4 flex-shrink-0" />
            </x-navigation.link>
        </x-slot>

        <x-navigation.dropdown-link href="{{ route('portal.manager.dashboard') }}" icon="📊">
                Dashboard
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('portal.manager.team-attendance') }}" icon="👥">
                Team Attendance
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('portal.manager.reports') }}" icon="📈">
                Reports
            </x-navigation.dropdown-link>
    </x-navigation.dropdown>
@endif

{{-- Portal Navigation - HR Admin Portal --}}
@if (auth()->check() && auth()->user()->hasRole(['hr_admin', 'admin']))
    <x-navigation.dropdown align="left" width="56">
        <x-slot name="trigger">
            <x-navigation.link href="#" icon="👨‍💼" :active="request()->routeIs(['hrm.dashboard', 'portal.manager.*', 'attendance.dashboard', 'payroll.dashboard', 'hr.employees.*'])">
                HR Admin Portal
                <x-heroicon-m-chevron-down class="ml-1 -mr-0.5 h-4 w-4 flex-shrink-0" />
            </x-navigation.link>
        </x-slot>

        <x-navigation.dropdown-link href="{{ route('hrm.dashboard') }}" icon="📊">
                Dashboard
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('portal.manager.reports') }}" icon="📅">
                Leave Approval
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('attendance.dashboard') }}" icon="⏱️">
                Attendance Admin
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('payroll.dashboard') }}" icon="💰">
                Payroll Admin
            </x-navigation.dropdown-link>

            <x-navigation.dropdown-link href="{{ route('hr.employees.index') }}" icon="👥">
                Employee Management
            </x-navigation.dropdown-link>
    </x-navigation.dropdown>
@endif
