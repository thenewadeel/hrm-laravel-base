<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\OrganizationUnit;
use App\Models\OrganizationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $query = Employee::with(['user', 'organizationUnit'])
            ->where('organization_id', $currentOrganizationId)
            ->where('is_active', true);

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->where('organization_unit_id', $request->department);
        }

        $employees = $query->paginate(20);
        $departments = OrganizationUnit::where('organization_id', $currentOrganizationId)->get();

        return view('hr.employees.index', compact('employees', 'departments'));
    }


    public function create()
    {
        $organizationUnits = OrganizationUnit::where(
            'organization_id',
            auth()->user()->current_organization_id
        )->get();

        return view('hr.employees.create', compact('organizationUnits'));
    }

    public function store(Request $request)
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')->where(function ($query) use ($currentOrganizationId) {
                    return $query->where('organization_id', $currentOrganizationId);
                }),
                Rule::unique('users', 'email')
            ],
            'password' => 'required|confirmed|min:8',
            'position' => 'required|string|max:255',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'roles' => 'required|array',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'biometric_id' => 'nullable|string|max:50|unique:employees,biometric_id',
            'required_daily_hours' => 'nullable|numeric|min:0|max:24',
            'salary_per_month' => 'nullable|numeric|min:0',
            'pay_frequency' => 'nullable|in:monthly,biweekly,weekly',
            'is_admin' => 'nullable|boolean'
        ]);

        // Create user account for system access
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'current_organization_id' => $currentOrganizationId,
        ]);

        // Create employee record
        $employee = Employee::create([
            'user_id' => $user->id,
            'organization_id' => $currentOrganizationId,
            'organization_unit_id' => $validated['organization_unit_id'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'biometric_id' => $validated['biometric_id'] ?? null,
            'is_admin' => $validated['is_admin'] ?? false,
            'is_active' => true,
        ]);

        // Create organization user relationship for permissions/roles
        OrganizationUser::create([
            'user_id' => $user->id,
            'organization_id' => $currentOrganizationId,
            'organization_unit_id' => $validated['organization_unit_id'] ?? null,
            'roles' => $validated['roles'],
            'position' => $validated['position'],
        ]);

        return redirect()->route('hr.employees.index')
            ->with('success', 'Employee created successfully!');
    }
    public function show(Employee $employee)
    {
        // Authorization check - ensure employee belongs to same organization
        // $this->authorize('view', $employee);

        $employee->load([
            'user',
            'organizationUnit',
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

        // Load organization user data for roles and permissions
        if ($employee->user_id) {
            $employee->load(['user.currentOrganizationUser']);
        }

        return view('hr.employees.show', compact('employee'));
    }

    public function updateBiometric(Request $request, Employee $employee)
    {
        // $this->authorize('update', $employee);

        $validated = $request->validate([
            'biometric_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'biometric_id')->ignore($employee->id)
            ]
        ]);

        $employee->update($validated);

        return redirect()->route('hr.employees.show', $employee)
            ->with('success', 'Biometric ID updated successfully!');
    }

    public function update(Request $request, Employee $employee)
    {
        // $this->authorize('update', $employee);

        $currentOrganizationId = auth()->user()->current_organization_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')
                    ->where(function ($query) use ($currentOrganizationId) {
                        return $query->where('organization_id', $currentOrganizationId);
                    })
                    ->ignore($employee->id),
                Rule::unique('users', 'email')->ignore($employee->user_id)
            ],
            'position' => 'required|string|max:255',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'salary_per_month' => 'nullable|numeric|min:0',
            'required_daily_hours' => 'nullable|numeric|min:0|max:24',
            'is_admin' => 'nullable|boolean',
            'is_active' => 'nullable|boolean'
        ]);

        // Update employee record
        $employee->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'organization_unit_id' => $validated['organization_unit_id'] ?? null,
            'is_admin' => $validated['is_admin'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Update user account if exists
        if ($employee->user_id) {
            $employee->user->update([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
            ]);

            // Update organization user relationship
            OrganizationUser::where('user_id', $employee->user_id)
                ->where('organization_id', $currentOrganizationId)
                ->update([
                    'position' => $validated['position'],
                    'organization_unit_id' => $validated['organization_unit_id'] ?? null,
                ]);
        }

        return redirect()->route('hr.employees.show', $employee)
            ->with('success', 'Employee updated successfully!');
    }

    /**
     * Create employee without user account (HR record only)
     */
    public function storeWithoutUser(Request $request)
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')->where(function ($query) use ($currentOrganizationId) {
                    return $query->where('organization_id', $currentOrganizationId);
                })
            ],
            'position' => 'required|string|max:255',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'biometric_id' => 'nullable|string|max:50|unique:employees,biometric_id',
            'required_daily_hours' => 'nullable|numeric|min:0|max:24',
            'salary_per_month' => 'nullable|numeric|min:0',
            'pay_frequency' => 'nullable|in:monthly,biweekly,weekly',
            'is_admin' => 'nullable|boolean'
        ]);

        // Create employee record only (no user account)
        $employee = Employee::create([
            'user_id' => null, // No user account
            'organization_id' => $currentOrganizationId,
            'organization_unit_id' => $validated['organization_unit_id'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'biometric_id' => $validated['biometric_id'] ?? null,
            'is_admin' => $validated['is_admin'] ?? false,
            'is_active' => true,
        ]);

        return redirect()->route('hr.employees.index')
            ->with('success', 'Employee record created successfully! (No system access)');
    }

    /**
     * Grant system access to existing employee
     */
    public function grantSystemAccess(Request $request, Employee $employee)
    {
        // $this->authorize('update', $employee);

        if ($employee->user_id) {
            return redirect()->back()
                ->with('error', 'Employee already has system access!');
        }

        $validated = $request->validate([
            'roles' => 'required|array',
            'position' => 'required|string|max:255',
            'password' => 'required|confirmed|min:8',
        ]);

        // Create user account
        $user = User::create([
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'email' => $employee->email,
            'password' => Hash::make($validated['password']),
            'current_organization_id' => $employee->organization_id,
        ]);

        // Update employee with user_id
        $employee->update(['user_id' => $user->id]);

        // Create organization user relationship
        OrganizationUser::create([
            'user_id' => $user->id,
            'organization_id' => $employee->organization_id,
            'organization_unit_id' => $employee->organization_unit_id,
            'roles' => $validated['roles'],
            'position' => $validated['position'],
        ]);

        return redirect()->route('hr.employees.show', $employee)
            ->with('success', 'System access granted successfully!');
    }
}
