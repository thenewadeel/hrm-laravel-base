<?php

namespace App\Http\Controllers\Api\HRM;

use App\Events\EmployeeDeleted;
use App\Events\EmployeeUpdated;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $query = Employee::with(['user', 'organizationUnit'])
            ->where('organization_id', $currentOrganizationId);

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->search.'%')
                    ->orWhere('last_name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->where('organization_unit_id', $request->department);
        }

        $employees = $query->orderBy('is_active', 'desc')
            ->orderBy('last_name', 'asc')
            ->paginate(20);

        // Load organization user data manually
        $employeeIds = $employees->getCollection()->pluck('id');
        $organizationUsers = OrganizationUser::whereIn('user_id',
            Employee::whereIn('id', $employeeIds)->pluck('user_id')
        )->where('organization_id', $currentOrganizationId)->get()->keyBy('user_id');

        $employees->getCollection()->each(function ($employee) use ($organizationUsers) {
            $employee->organizationUser = $organizationUsers->get($employee->user_id);
        });

        return response()->json([
            'data' => $employees->items(),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ],
        ]);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'hire_date' => 'required|date|before_or_equal:today',
            'employee_id' => 'required|string|max:50|unique:employees,employee_id',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'position' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $organizationId = auth()->user()->current_organization_id;

        $employeeData = array_merge($request->all(), [
            'organization_id' => $organizationId,
            'is_active' => $request->get('is_active', true),
        ]);

        $employee = Employee::create($employeeData);

        return response()->json([
            'success' => true,
            'data' => $employee->load(['user', 'organizationUnit']),
            'message' => 'Employee created successfully.',
        ], 201);
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        $employee->load(['user', 'organizationUnit']);

        return response()->json([
            'success' => true,
            'data' => $employee,
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('update', $employee);

        $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => ['sometimes', 'email', Rule::unique('employees')->ignore($employee->id)],
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'date_of_birth' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female,other',
            'hire_date' => 'sometimes|date|before_or_equal:today',
            'employee_id' => ['sometimes', 'string', 'max:50', Rule::unique('employees')->ignore($employee->id)],
            'organization_unit_id' => 'sometimes|exists:organization_units,id',
            'position' => 'sometimes|string|max:100',
            'salary' => 'sometimes|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $employee->update($request->all());

        // Dispatch audit event
        event(new EmployeeUpdated(auth()->user(), $employee));

        return response()->json([
            'success' => true,
            'data' => $employee->load(['user', 'organizationUnit']),
            'message' => 'Employee updated successfully.',
        ]);
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        // Dispatch audit event before deletion
        event(new EmployeeDeleted(auth()->user(), $employee));

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully.',
        ]);
    }

    /**
     * Export employees to CSV.
     */
    public function export(Request $request): JsonResponse
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $employees = Employee::with(['user', 'organizationUnit'])
            ->where('organization_id', $currentOrganizationId)
            ->get();

        $csvData = $employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'employee_id' => $employee->employee_id,
                'phone' => $employee->phone,
                'position' => $employee->position,
                'department' => $employee->organizationUnit?->name,
                'hire_date' => $employee->hire_date,
                'is_active' => $employee->is_active,
                'organization_id' => $employee->organization_id,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $csvData,
        ]);
    }
}
