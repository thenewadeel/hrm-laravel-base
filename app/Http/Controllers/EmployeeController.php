<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OrganizationUnit;
use App\Models\OrganizationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereHas('organizationMemberships', function ($q) {
            $q->where('organization_id', auth()->user()->current_organization_id);
        })->with(['currentOrganizationUser.organizationUnit']);

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->whereHas('currentOrganizationUser', function ($q) use ($request) {
                $q->where('organization_unit_id', $request->department);
            });
        }

        $employees = $query->paginate(20);
        $departments = OrganizationUnit::where('organization_id', auth()->user()->current_organization_id)->get();

        return view('hr.employees.index', compact('employees', 'departments'));
    }

    public function show(User $employee)
    {
        // Authorization check - ensure employee belongs to same organization
        $this->authorize('view', $employee);

        $employee->load([
            'currentOrganizationUser.organizationUnit',
            'attendanceRecords' => function ($q) {
                $q->latest()->take(10);
            },
            'leaveRequests' => function ($q) {
                $q->latest()->take(5);
            },
            'payrollEntries' => function ($q) {
                $q->latest()->take(3);
            }
        ]);

        return view('hr.employees.show', compact('employee'));
    }

    public function create()
    {
        $organizationUnits = OrganizationUnit::where('organization_id', auth()->user()->current_organization_id)->get();
        return view('hr.employees.create', compact('organizationUnits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'position' => 'required|string|max:255',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'roles' => 'required|array',
            'biometric_id' => 'nullable|string|max:50',
            'required_daily_hours' => 'nullable|numeric|min:0',
            'salary_per_month' => 'nullable|numeric|min:0',
            'pay_frequency' => 'nullable|in:monthly,biweekly,weekly'
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'current_organization_id' => auth()->user()->current_organization_id,
            'biometric_id' => $validated['biometric_id'] ?? null,
        ]);

        // Create organization user relationship
        OrganizationUser::create([
            'user_id' => $user->id,
            'organization_id' => auth()->user()->current_organization_id,
            'organization_unit_id' => $validated['organization_unit_id'] ?? null,
            'roles' => $validated['roles'],
            'position' => $validated['position'],
        ]);

        return redirect()->route('hr.employees.index')
            ->with('success', 'Employee created successfully!');
    }

    // In App\Http\Controllers\HR\EmployeeController.php

    public function updateBiometric(Request $request, Employee $employee)
    {
        $this->authorize('update', $employee);

        $validated = $request->validate([
            'biometric_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'biometric_id')->ignore($employee->id)
            ]
        ]);

        // Debug: Check what we're receiving
        \Log::info('Updating biometric ID', [
            'employee_id' => $employee->id,
            'current_biometric' => $employee->biometric_id,
            'new_biometric' => $validated['biometric_id']
        ]);

        $employee->update([
            'biometric_id' => $validated['biometric_id']
        ]);

        return redirect()->route('hr.employees.show', $employee)
            ->with('success', 'Biometric ID updated successfully!');
    }

    public function update(Request $request, User $employee)
    {
        $this->authorize('update', $employee);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'position' => 'required|string|max:255',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'salary_per_month' => 'nullable|numeric|min:0',
            'required_daily_hours' => 'nullable|numeric|min:0'
        ]);

        // Update user
        $employee->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update organization user relationship
        OrganizationUser::where('user_id', $employee->id)
            ->where('organization_id', auth()->user()->current_organization_id)
            ->update([
                'position' => $validated['position'],
                'organization_unit_id' => $validated['organization_unit_id'] ?? null,
            ]);

        return redirect()->route('hr.employees.show', $employee)
            ->with('success', 'Employee updated successfully!');
    }
}
