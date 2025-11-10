## Attendance and Payroll

Here is a plan to integrate Attendance and Payroll, broken down into manageable steps, addressing the missing `Employee` class and the biometric data challenge.

---

## Step 1: Establish the "Employee" Foundation

You are right, your project is missing a dedicated `Employee` model. Since you're using **Laravel Jetstream**, your authenticated users are already stored in the **`users`** table and represented by the `User` model.

The best approach is to leverage the existing `User` model and link new HR-specific data to it.

1.  **Create the `EmployeeDetails` Model (or similar):**
    -   This model will hold all the HR and payroll-related information that is _not_ needed for basic authentication (e.g., salary, job title, department ID, biometric ID, date of joining).
    -   It will have a **`hasOne`** relationship with the existing `User` model (`User` has one `EmployeeDetails`, and `EmployeeDetails` belongs to one `User`).

| Migration (`create_employee_details_table`) | Purpose                                                               |
| :------------------------------------------ | :-------------------------------------------------------------------- |
| `user_id` (foreign key)                     | Links back to the `users` table.                                      |
| `employee_id_number` (string)               | Your internal HR number (e.g., 'EMP-001').                            |
| `biometric_id` (string)                     | The ID number used by the thumb reader/device. **CRITICAL for sync.** |
| `salary_per_month` (decimal)                | The base pay for payroll calculation.                                 |
| `organization_unit_id` (foreign key)        | Links to your existing `OrganizationUnit` (Department/Branch).        |

## Step 2: Attendance Tracking & Biometric Integration

This is the scariest part, but it's simpler than you think. You don't program the thumb reader itself; you just need to accept data from it.

### A. The Attendance Log Table

Create a simple model to store the raw punches.

| Migration (`create_attendance_logs_table`) | Purpose                                                                |
| :----------------------------------------- | :--------------------------------------------------------------------- |
| `biometric_id` (string)                    | The ID sent by the device.                                             |
| `punch_time` (datetime)                    | The exact timestamp of the punch.                                      |
| `punch_type` (enum: 'IN', 'OUT')           | What the employee was doing.                                           |
| `device_serial_number` (string)            | Which physical device recorded the punch.                              |
| `user_id` (nullable foreign key)           | We will fill this **after** we sync the `biometric_id` to a `user_id`. |

### B. Syncing with the Device

Most thumb readers (like ZKTeco devices) don't send data in real-time. They either:

1.  **Store data locally** and allow you to **pull** the records via a scheduled Laravel command (`php artisan sync:attendance`).
2.  **Export a raw CSV/TXT file** that HR uploads manually.

**For a software solution, focus on a middleware API:**

-   **External Service/SDK:** You will need a PHP or Python library that can communicate with the specific biometric device's API or SDK (often over a local network connection).
-   **Laravel Command:** Write a scheduled command that runs every hour:
    1.  Connect to the device using the external library.
    2.  Fetch all new raw punches.
    3.  Insert them into your `attendance_logs` table.

### C. The **Attendance + Leave Sync** Logic

This is where the magic from your original diagram happens.

1.  **Create a `DailyAttendanceRecord` Table/Model:** This holds the final, reconciled attendance status for payroll.

2.  **The Processing Command:** Create a command (`php artisan process:attendance`) that runs nightly.

    | **Processing Steps**   | **Check/Action**                                                                                                                                                                                             |
    | :--------------------- | :----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
    | **Identify User**      | Look up the `biometric_id` in the `employee_details` table to find the associated `user_id`.                                                                                                                 |
    | **Pair Punches**       | Group all 'IN' and 'OUT' punches for that user on that day to calculate total **Time Worked**.                                                                                                               |
    | **Check Leave Status** | Query your (soon-to-be-built) `LeaveRequest` table. Was approved leave taken on that day?                                                                                                                    |
    | **Finalize Status**    | **IF** Approved Leave Exists, set status to **'PAID_LEAVE'**. **ELSE IF** Total Time Worked is less than required, set status to **'PRESENT_SHORT_HOURS'**. **ELSE** Set status to **'PRESENT_FULL_HOURS'**. |
    | **Save Record**        | Save the final status, total time, and the relevant `user_id` to the `daily_attendance_records` table.                                                                                                       |

## Step 3: Payroll Integration

Your existing project is **perfect** for payroll integration because you already have the **Accounting Module**!

The goal is to calculate the salary and generate a **Journal Entry** to post the transaction.

### A. The `PayrollRun` Model

This model tracks the batch calculation for a pay period.

| Migration (`create_payroll_runs_table`) | Purpose                                               |
| :-------------------------------------- | :---------------------------------------------------- |
| `period_start`, `period_end`            | The pay cycle dates (e.g., 2025-10-01 to 2025-10-31). |
| `status` (enum)                         | 'Calculated', 'Approved', 'Paid'.                     |

### B. The `PayrollSlip` Model (The Final Calculation)

This links the run to the employee and stores all the final numbers.

| Migration (`create_payroll_slips_table`) | Purpose                                        |
| :--------------------------------------- | :--------------------------------------------- |
| `payroll_run_id` (foreign key)           | Links to the batch run.                        |
| `user_id` (foreign key)                  | The employee receiving the pay.                |
| `gross_pay` (decimal)                    | Base salary + Overtime.                        |
| `deductions` (decimal)                   | Taxes, LOP (Loss of Pay from **Step 2**), etc. |
| `net_pay` (decimal)                      | The final amount to be paid.                   |

### C. The Final Accounting Entry

When the `PayrollRun` is approved, you use your existing **`JournalEntry`** and **`LedgerEntry`** models to record the expense:

1.  **Debit (Expense):** Debit the **Salary Expense** Account (an `Expense` account) for the total `Gross Pay` of all employees.
2.  **Credit (Liability):** Credit the **Payable (Net Salary)** Account (a `Liability` account) for the total `Net Pay`.
3.  **Credit (Liability):** Credit the **Tax/Deduction Payable** Accounts (Liabilities) for the total deductions.

This completes the loop and keeps your financials accurate. You have all the models needed for the accounting part already defined in your existing structure.

---

# Development Scrum Summary: HR, Attendance & Payroll Integration

## 🎯 Sprint Goal

Integrate HR, Attendance, and Payroll modules with existing Accounting system using Test-Driven Development (TDD) approach.

## 📋 User Stories & Acceptance Criteria

### **HR Module**

**Story 1:** As an HR Manager, I want to view and manage employee details so that I can maintain accurate employee records.

**Acceptance Criteria:**

-   ✅ Employee profile view with personal, HR, attendance, and payroll sections
-   ✅ Biometric ID management for attendance tracking
-   ✅ Organization unit assignment
-   ✅ Payroll setup integration

**Story 2:** As an HR Manager, I want to browse all employees with essential information.

**Acceptance Criteria:**

-   ✅ Employee listing with search/filter capabilities
-   ✅ Quick access to individual employee profiles
-   ✅ Department and status filtering

### **Attendance Module**

**Story 3:** As a Manager, I want to review and manage attendance exceptions before payroll processing.

**Acceptance Criteria:**

-   ✅ Attendance dashboard with exception highlighting
-   ✅ Date range and department filtering
-   ✅ Color-coded status indicators (Late, Absent, Missed Punch)
-   ✅ Manager actions: Regularize Time, Apply Leave
-   ✅ Summary metrics (Absent Days, Late Records, Overtime)

### **Payroll Module**

**Story 4:** As a Payroll Administrator, I want to process payroll and generate accounting entries.

**Acceptance Criteria:**

-   ✅ Payroll run management with status tracking
-   ✅ Employee payroll calculations with attendance integration
-   ✅ Accounting integration with Chart of Accounts
-   ✅ Journal entry generation for payroll transactions
-   ✅ Individual payslip generation

## 🧪 TDD Implementation Plan

### **Phase 1: Test Setup & Foundation**

```bash
# Setup testing environment
php artisan make:test HR/EmployeeManagementTest
php artisan make:test Attendance/AttendanceSyncTest
php artisan make:test Payroll/PayrollProcessingTest
```

### **Phase 2: HR Module Tests**

```php
// tests/Feature/HR/EmployeeManagementTest.php
public function test_can_view_employee_list()
public function test_can_view_employee_details()
public function test_can_create_employee()
public function test_can_update_biometric_id()
public function test_employee_belongs_to_organization_unit()
```

### **Phase 3: Attendance Module Tests**

```php
// tests/Feature/Attendance/AttendanceSyncTest.php
public function test_can_view_attendance_dashboard()
public function test_can_filter_attendance_by_date_range()
public function test_can_identify_attendance_exceptions()
public function test_can_regularize_missed_punch()
public function test_attendance_data_integrates_with_payroll()
```

### **Phase 4: Payroll Module Tests**

```php
// tests/Feature/Payroll/PayrollProcessingTest.php
public function test_can_calculate_payroll_with_attendance()
public function test_payroll_includes_correct_deductions()
public function test_can_generate_accounting_journal_entry()
public function test_payslip_generation()
public function test_payroll_uses_correct_chart_of_accounts()
```

## 🔄 Integration Points

### **Database Relationships**

```php
// User model additions
public function attendanceRecords()
{
return $this->hasMany(AttendanceRecord::class);
}

public function payrollEntries()
{
return $this->hasMany(PayrollEntry::class);
}

public function organizationUnit()
{
return $this->belongsTo(OrganizationUnit::class);
}
```

### **Accounting Integration**

```php
// Payroll Service
public function createPayrollJournalEntry($payrollRun)
{
return JournalEntry::create([
'organization_id' => $payrollRun->organization_id,
'reference_number' => 'PAY-' . $payrollRun->period,
'entry_date' => now(),
'description' => 'Payroll for ' . $payrollRun->period,
'status' => 'posted',
// Debit Salary Expense, Credit Payroll Payable
]);
}
```

## 🗓️ Implementation Steps

### **Week 1: Foundation & HR Module**

1. ✅ Create database migrations for new tables
2. ✅ Implement HR Employee Management views
3. 🧪 Write and pass HR module tests
4. 🔗 Integrate with existing Organization structure

### **Week 2: Attendance Module**

1. ✅ Create attendance dashboard and views
2. 🧪 Write attendance sync and exception handling tests
3. 🔗 Integrate biometric data processing
4. 🧪 Test attendance-payroll data flow

### **Week 3: Payroll Module**

1. ✅ Implement payroll processing views
2. 🧪 Write payroll calculation tests
3. 🔗 Integrate with existing Accounting JournalEntries
4. 🧪 Test accounting entry generation

### **Week 4: Integration & Polish**

1. 🧪 End-to-end integration tests
2. 🔗 Finalize all module integrations
3. 🎨 UI/UX polish and validation
4. 📚 Documentation and deployment

## 🎯 Key Technical Considerations

### **Existing Integration Points**

-   **Organization Structure**: Use existing `Organization` and `OrganizationUnit` models
-   **Accounting Module**: Leverage existing `JournalEntry`, `ChartOfAccount` models
-   **Authentication**: Use existing Laravel Jetstream auth system

### **New Database Tables Needed**

```php
// attendance_records
Schema::create('attendance_records', function (Blueprint $table) {
$table->id();
$table->foreignId('user_id')->constrained();
$table->date('record_date');
$table->timestamp('punch_in')->nullable();
$table->timestamp('punch_out')->nullable();
$table->decimal('total_hours', 5, 2)->default(0);
$table->enum('status', ['present', 'absent', 'late', 'leave', 'missed_punch']);
$table->string('biometric_id');
$table->timestamps();
});

// payroll_runs
Schema::create('payroll_runs', function (Blueprint $table) {
$table->id();
$table->string('period');
$table->date('start_date');
$table->date('end_date');
$table->enum('status', ['draft', 'calculated', 'processed', 'paid']);
$table->decimal('total_gross', 12, 2);
$table->decimal('total_net', 12, 2);
$table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
$table->timestamps();
});
```

## 🚀 Success Metrics

-   ✅ All tests pass (unit, feature, integration)
-   ✅ Seamless data flow: HR → Attendance → Payroll → Accounting
-   ✅ Real-time attendance exception handling
-   ✅ Automated accounting entry generation
-   ✅ Audit trail for all payroll transactions

## 🔄 Next Steps

1. Create database migrations for new tables
2. Implement model relationships and business logic
3. Write comprehensive test suite following TDD
4. Integrate with existing authentication and authorization
5. Deploy and validate end-to-end workflow

Ready to start with the TDD implementation! 🧪🚀

---

# Organization Management Dashboard - Enhancement Proposal

Looking at your existing structure, you have solid foundations with `Organization`, `OrganizationUnit`, and user relationships. Let me suggest a comprehensive Organization Management Dashboard that builds on what you already have.

## 🎯 Current Gaps & Opportunities

### **Existing Assets:**

-   ✅ `Organization` model with active/inactive status
-   ✅ `OrganizationUnit` model with hierarchical structure
-   ✅ `OrganizationUser` pivot with roles/permissions
-   ✅ `BelongsToOrganization` trait for scoping
-   ✅ Organization tree views in Livewire

### **Missing Management Layer:**

-   ❌ Centralized organization dashboard
-   ❌ Performance metrics and KPIs
-   ❌ Workforce analytics
-   ❌ Department-level reporting
-   ❌ Organization health monitoring

## 🏗️ Proposed Organization Management Dashboard

### **1. Organization Overview Dashboard**

**File:** `resources/views/organization/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Organization Management Dashboard')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Organization Management
                </h2>
                <p class="mt-1 text-sm text-gray-500">Comprehensive overview of your organizational structure and performance</p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Export Report
                </button>
                <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Manage Structure
                </button>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Total Employees -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Employees</dt>
                                <dd class="text-lg font-semibold text-gray-900">247</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <span class="text-green-600 font-medium">+12% </span>
                        <span class="text-gray-500">from last month</span>
                    </div>
                </div>
            </div>

            <!-- Departments -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Departments</dt>
                                <dd class="text-lg font-semibold text-gray-900">14</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <span class="text-gray-500">Across 3 locations</span>
                    </div>
                </div>
            </div>

            <!-- Attendance Rate -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Attendance Rate</dt>
                                <dd class="text-lg font-semibold text-gray-900">94.2%</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <span class="text-green-600 font-medium">+2.1% </span>
                        <span class="text-gray-500">improvement</span>
                    </div>
                </div>
            </div>

            <!-- Payroll Cost -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 6v1m0-1v1" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Monthly Payroll</dt>
                                <dd class="text-lg font-semibold text-gray-900">$1.2M</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <span class="text-red-600 font-medium">+5.3% </span>
                        <span class="text-gray-500">from last month</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Structure -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Department Distribution -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Employee Distribution by Department
                    </h3>
                    <div class="space-y-4">
                        @foreach($departmentStats as $dept)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $dept['name'] }}</span>
                                <span class="text-sm text-gray-500">{{ $dept['count'] }} ({{ $dept['percentage'] }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $dept['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Organization Health -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Organization Health Metrics
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Employee Satisfaction</span>
                                <span class="text-sm text-gray-500">82%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: 82%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Retention Rate</span>
                                <span class="text-sm text-gray-500">88%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: 88%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Role Utilization</span>
                                <span class="text-sm text-gray-500">76%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-600 h-2 rounded-full" style="width: 76%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities & Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Organization Structure Preview -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Organization Structure
                        </h3>
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <!-- Simplified org chart preview -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-center">
                                    <div class="bg-blue-100 border border-blue-300 rounded-lg px-4 py-2 text-sm font-medium text-blue-800">
                                        CEO / Executive
                                    </div>
                                </div>
                                <div class="flex justify-center space-x-8">
                                    <div class="text-center">
                                        <div class="bg-green-100 border border-green-300 rounded-lg px-3 py-1 text-xs font-medium text-green-800 mb-1">
                                            Engineering
                                        </div>
                                        <div class="text-xs text-gray-500">45 employees</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="bg-purple-100 border border-purple-300 rounded-lg px-3 py-1 text-xs font-medium text-purple-800 mb-1">
                                            Sales
                                        </div>
                                        <div class="text-xs text-gray-500">32 employees</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="bg-yellow-100 border border-yellow-300 rounded-lg px-3 py-1 text-xs font-medium text-yellow-800 mb-1">
                                            Marketing
                                        </div>
                                        <div class="text-xs text-gray-500">28 employees</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('organization.structure') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                View Full Organization Chart →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Quick Actions
                    </h3>
                    <div class="space-y-3">
                        <a href="{{ route('hr.employees.create') }}" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Add New Employee
                        </a>
                        <a href="{{ route('organization.units.create') }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Create Department
                        </a>
                        <a href="{{ route('attendance.dashboard') }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            View Attendance
                        </a>
                        <a href="{{ route('payroll.processing') }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Process Payroll
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### **2. Enhanced Organization Management Controller**

**File:** `app/Http/Controllers/Organization/OrganizationDashboardController.php`

```php
<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationDashboardController extends Controller
{
    /**
     * Display organization management dashboard.
     */
    public function dashboard()
    {
        $organization = Organization::first(); // Or get current organization

        $metrics = $this->getOrganizationMetrics($organization);
        $departmentStats = $this->getDepartmentStatistics($organization);
        $recentActivities = $this->getRecentActivities($organization);

        return view('organization.dashboard', compact('metrics', 'departmentStats', 'recentActivities'));
    }

    /**
     * Get organization-wide metrics.
     */
    private function getOrganizationMetrics(Organization $organization)
    {
        // Mock data - replace with actual queries
        return [
            'total_employees' => 247,
            'total_departments' => 14,
            'attendance_rate' => 94.2,
            'monthly_payroll' => 1200000,
            'employee_growth' => 12, // percentage
            'payroll_growth' => 5.3, // percentage
            'attendance_improvement' => 2.1 // percentage
        ];
    }

    /**
     * Get department-level statistics.
     */
    private function getDepartmentStatistics(Organization $organization)
    {
        // Mock data - replace with actual department aggregation
        return [
            ['name' => 'Engineering', 'count' => 45, 'percentage' => 18],
            ['name' => 'Sales', 'count' => 32, 'percentage' => 13],
            ['name' => 'Marketing', 'count' => 28, 'percentage' => 11],
            ['name' => 'Human Resources', 'count' => 18, 'percentage' => 7],
            ['name' => 'Finance', 'count' => 22, 'percentage' => 9],
            ['name' => 'Operations', 'count' => 35, 'percentage' => 14],
            ['name' => 'Customer Support', 'count' => 42, 'percentage' => 17],
            ['name' => 'Other', 'count' => 25, 'percentage' => 10],
        ];
    }

    /**
     * Get recent organizational activities.
     */
    private function getRecentActivities(Organization $organization)
    {
        // Mock data - replace with actual activity log
        return [
            [
                'type' => 'employee_added',
                'description' => 'John Smith joined Engineering department',
                'time' => '2 hours ago',
                'icon' => 'user-add'
            ],
            [
                'type' => 'department_created',
                'description' => 'New "Research & Development" department created',
                'time' => '1 day ago',
                'icon' => 'folder-add'
            ],
            [
                'type' => 'attendance_regularized',
                'description' => '15 attendance records regularized',
                'time' => '2 days ago',
                'icon' => 'clock'
            ],
            [
                'type' => 'payroll_processed',
                'description' => 'October payroll processed successfully',
                'time' => '3 days ago',
                'icon' => 'currency-dollar'
            ]
        ];
    }

    /**
     * Get organization structure tree.
     */
    public function structure()
    {
        $organization = Organization::first();
        $tree = OrganizationUnit::with(['children', 'users'])->whereNull('parent_id')->get();

        return view('organization.structure', compact('organization', 'tree'));
    }

    /**
     * Get organization analytics report.
     */
    public function analytics(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth());
        $endDate = $request->get('end_date', now());

        $analytics = [
            'headcount_trend' => $this->getHeadcountTrend($startDate, $endDate),
            'attendance_trend' => $this->getAttendanceTrend($startDate, $endDate),
            'department_performance' => $this->getDepartmentPerformance($startDate, $endDate),
            'cost_analysis' => $this->getCostAnalysis($startDate, $endDate)
        ];

        return view('organization.analytics', compact('analytics'));
    }

    // Additional methods for data aggregation...
    private function getHeadcountTrend($startDate, $endDate) { /* Implementation */ }
    private function getAttendanceTrend($startDate, $endDate) { /* Implementation */ }
    private function getDepartmentPerformance($startDate, $endDate) { /* Implementation */ }
    private function getCostAnalysis($startDate, $endDate) { /* Implementation */ }
}
```

## 🧪 TDD Test Cases for Organization Management

**File:** `tests/Feature/Organization/OrganizationDashboardTest.php`

```php
<?php

namespace Tests\Feature\Organization;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $organization;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'current_organization_id' => $this->organization->id
        ]);
    }

    /** @test */
    public function authenticated_user_can_access_organization_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('organization.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Organization Management');
    }

    /** @test */
    public function dashboard_displays_correct_organization_metrics()
    {
        // Create test data
        $engineering = OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Engineering'
        ]);

        User::factory()->count(5)->create([
            'current_organization_id' => $this->organization->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('organization.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Employees');
        $response->assertSee('Departments');
    }

    /** @test */
    public function user_can_view_organization_structure()
    {
        $parentUnit = OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id,
            'parent_id' => null
        ]);

        $childUnit = OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id,
            'parent_id' => $parentUnit->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('organization.structure'));

        $response->assertStatus(200);
        $response->assertSee($parentUnit->name);
        $response->assertSee($childUnit->name);
    }

    /** @test */
    public function dashboard_includes_department_distribution_data()
    {
        // Create multiple departments with employees
        $departments = ['Engineering', 'Sales', 'Marketing'];

        foreach ($departments as $dept) {
            $unit = OrganizationUnit::factory()->create([
                'organization_id' => $this->organization->id,
                'name' => $dept
            ]);

            User::factory()->count(rand(3, 8))->create([
                'current_organization_id' => $this->organization->id
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('organization.dashboard'));

        $response->assertStatus(200);
        foreach ($departments as $dept) {
            $response->assertSee($dept);
        }
    }

    /** @test */
    public function analytics_page_displays_trend_data()
    {
        $response = $this->actingAs($this->user)
            ->get(route('organization.analytics'));

        $response->assertStatus(200);
        $response->assertSee('Analytics');
        $response->assertSee('Headcount Trend');
        $response->assertSee('Performance Metrics');
    }
}
```

## 🚀 Implementation Roadmap

### **Phase 1: Core Dashboard (Week 1)**

1. Create OrganizationDashboardController with mock data
2. Build main dashboard view with metrics cards
3. Implement department distribution charts
4. Add quick actions panel

### **Phase 2: Data Integration (Week 2)**

1. Replace mock data with actual database queries
2. Implement organization structure tree view
3. Add attendance and payroll integration
4. Create department performance metrics

### **Phase 3: Advanced Analytics (Week 3)**

1. Build analytics page with date filtering
2. Implement trend charts and comparisons
3. Add export functionality
4. Create department-level dashboards

### **Phase 4: Polish & Integration (Week 4)**

1. Add real-time updates with Livewire
2. Implement role-based access control
3. Mobile responsiveness optimization
4. Performance optimization for large datasets

## 🔗 Integration Points with Existing Modules

```php
// Enhanced relationships in OrganizationUnit model
public function performanceMetrics()
{
    return $this->hasMany(DepartmentPerformance::class);
}

public function attendanceSummary()
{
    return $this->hasOne(AttendanceSummary::class);
}

public function payrollSummary()
{
    return $this->hasOne(PayrollSummary::class);
}
```

This organization management dashboard will provide:

-   **Executive Overview**: High-level metrics for quick decision-making
-   **Department Insights**: Deep dive into team performance and composition
-   **Trend Analysis**: Historical data for strategic planning
-   **Quick Actions**: Rapid access to common management tasks
-   **Integration Hub**: Central point connecting HR, Attendance, and Payroll modules

Ready to implement this with TDD? 🚀

---

Absolutely! You're right - we need dedicated portals for employee self-service and manager functions. Let me design these essential portals that complete the HR ecosystem.

## 🎯 Essential Portals Needed

### **1. Employee Self-Service Portal**

### **2. Manager Portal**

### **3. HR Admin Portal**

### **4. Attendance Kiosk Portal**

Let me create these with proper TDD approach:

## 📱 1. Employee Self-Service Portal

**File:** `resources/views/portal/employee/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Employee Portal')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="bg-white shadow sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            Welcome back, John!
                        </h2>
                        <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Senior Developer - Engineering
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Active - EMP-1001
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                        <!-- Quick Clock In/Out -->
                        <div class="bg-gray-50 px-4 py-3 rounded-lg">
                            <div class="text-sm font-medium text-gray-500">Current Status</div>
                            <div class="flex items-center space-x-2">
                                <div class="h-3 w-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-lg font-semibold text-gray-900">Clocked In</span>
                            </div>
                            <div class="text-xs text-gray-500">Since 09:00 AM</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Attendance Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Monthly Attendance</dt>
                                <dd class="text-lg font-semibold text-gray-900">94%</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('portal.employee.attendance') }}" class="font-medium text-blue-600 hover:text-blue-500">
                            View details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Leave Balance -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Leave Balance</dt>
                                <dd class="text-lg font-semibold text-gray-900">12 days</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('portal.employee.leave') }}" class="font-medium text-blue-600 hover:text-blue-500">
                            Apply for leave
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payslip -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 6v1m0-1v1" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Last Payslip</dt>
                                <dd class="text-lg font-semibold text-gray-900">Oct 2024</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('portal.employee.payslips') }}" class="font-medium text-blue-600 hover:text-blue-500">
                            View payslips
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Holidays -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Next Holiday</dt>
                                <dd class="text-lg font-semibold text-gray-900">Dec 25</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="{{ route('portal.employee.holidays') }}" class="font-medium text-blue-600 hover:text-blue-500">
                            View calendar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Quick Actions -->
            <div class="lg:col-span-2">
                <!-- Today's Timeline -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Today's Timeline
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Clocked In</p>
                                    <p class="text-sm text-gray-500">09:00 AM - On time</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Lunch Break</p>
                                    <p class="text-sm text-gray-500">01:00 PM - 02:00 PM</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Clocked Out</p>
                                    <p class="text-sm text-gray-500">06:00 PM - Expected</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Quick Actions
                        </h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <a href="{{ route('portal.employee.clock-in-out') }}" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Clock In/Out
                            </a>
                            <a href="{{ route('portal.employee.leave-request') }}" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Request Leave
                            </a>
                            <a href="{{ route('portal.employee.attendance-regularization') }}" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Regularize Time
                            </a>
                            <a href="{{ route('portal.employee.payslips') }}" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                View Payslips
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Notifications & Updates -->
            <div class="space-y-6">
                <!-- Notifications -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Notifications
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-2 w-2 bg-blue-600 rounded-full mt-2"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Payroll Processed</p>
                                    <p class="text-sm text-gray-500">October payroll has been processed</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-2 w-2 bg-green-600 rounded-full mt-2"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Leave Approved</p>
                                    <p class="text-sm text-gray-500">Your sick leave for Nov 20 has been approved</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Holidays -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Upcoming Holidays
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-900">Christmas Day</span>
                                <span class="text-sm text-gray-500">Dec 25, 2024</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-900">New Year's Day</span>
                                <span class="text-sm text-gray-500">Jan 1, 2025</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## 👨‍💼 2. Manager Portal

**File:** `resources/views/portal/manager/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Manager Portal')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Manager Portal
                </h2>
                <p class="mt-1 text-sm text-gray-500">Team management and oversight dashboard</p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Team Report
                </button>
                <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Approve Requests
                </button>
            </div>
        </div>

        <!-- Team Metrics -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Team Size -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Team Size</dt>
                                <dd class="text-lg font-semibold text-gray-900">12</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pending Approvals</dt>
                                <dd class="text-lg font-semibold text-gray-900">5</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Attendance -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Team Attendance</dt>
                                <dd class="text-lg font-semibold text-gray-900">92%</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- On Leave Today -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">On Leave Today</dt>
                                <dd class="text-lg font-semibold text-gray-900">2</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Team Overview -->
            <div class="lg:col-span-2">
                <!-- Team Attendance Today -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Team Attendance - Today
                        </h3>
                        <div class="space-y-3">
                            @foreach($teamAttendance as $member)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-300 rounded-full flex items-center justify-center">
                                        <span class="text-gray-600 font-medium text-sm">{{ $member['initials'] }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $member['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $member['role'] }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $member['status_class'] }}">
                                        {{ $member['status'] }}
                                    </span>
                                    <span class="text-sm text-gray-500">{{ $member['time'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Management Actions
                        </h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <a href="{{ route('portal.manager.attendance-approval') }}" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Approve Attendance
                            </a>
                            <a href="{{ route('portal.manager.leave-approval') }}" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Leave Requests
                            </a>
                            <a href="{{ route('portal.manager.team-report') }}" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Team Reports
                            </a>
                            <a href="{{ route('portal.manager.performance') }}" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Performance Reviews
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Approvals & Notifications -->
            <div class="space-y-6">
                <!-- Pending Approvals -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Pending Approvals
                        </h3>
                        <div class="space-y-4">
                            <div class="border-l-4 border-yellow-400 pl-4 py-2">
                                <p class="text-sm font-medium text-gray-900">Leave Request</p>
                                <p class="text-sm text-gray-500">Maria Johnson - Nov 20-22</p>
                                <div class="mt-2 flex space-x-2">
                                    <button class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Approve</button>
                                    <button class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Reject</button>
                                </div>
                            </div>
                            <div class="border-l-4 border-yellow-400 pl-4 py-2">
                                <p class="text-sm font-medium text-gray-900">Time Regularization</p>
                                <p class="text-sm text-gray-500">David Wilson - Nov 15</p>
                                <div class="mt-2 flex space-x-2">
                                    <button class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Approve</button>
                                    <button class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Reject</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team On Leave -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Team On Leave
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-900">Sarah Chen</span>
                                <span class="text-sm text-gray-500">Sick Leave</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-900">Mike Rodriguez</span>
                                <span class="text-sm text-gray-500">Vacation</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## 🧪 TDD Test Cases for Portals

**File:** `tests/Feature/Portal/EmployeePortalTest.php`

```php
<?php

namespace Tests\Feature\Portal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePortalTest extends TestCase
{
    use RefreshDatabase;

    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = User::factory()->create([
            'role' => 'employee'
        ]);
    }

    /** @test */
    public function employee_can_access_their_portal_dashboard()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('portal.employee.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Employee Portal');
        $response->assertSee('Welcome back');
    }

    /** @test */
    public function employee_can_view_their_attendance_records()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('portal.employee.attendance'));

        $response->assertStatus(200);
        $response->assertSee('Attendance Records');
    }

    /** @test */
    public function employee_can_apply_for_leave()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('portal.employee.leave-request'));

        $response->assertStatus(200);
        $response->assertSee('Apply for Leave');
    }

    /** @test */
    public function employee_can_view_their_payslips()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('portal.employee.payslips'));

        $response->assertStatus(200);
        $response->assertSee('Payslips');
    }

    /** @test */
    public function employee_can_clock_in_and_out()
    {
        $response = $this->actingAs($this->employee)
            ->post(route('portal.employee.clock-in'));

        $response->assertStatus(200);
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $this->employee->id,
            'type' => 'clock_in'
        ]);
    }
}
```

**File:** `tests/Feature/Portal/ManagerPortalTest.php`

```php
<?php

namespace Tests\Feature\Portal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected $manager;
    protected $teamMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create([
            'role' => 'manager'
        ]);

        $this->teamMember = User::factory()->create([
            'role' => 'employee',
            'manager_id' => $this->manager->id
        ]);
    }

    /** @test */
    public function manager_can_access_their_portal_dashboard()
    {
        $response = $this->actingAs($this->manager)
            ->get(route('portal.manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Manager Portal');
        $response->assertSee('Team management');
    }

    /** @test */
    public function manager_can_view_team_attendance()
    {
        $response = $this->actingAs($this->manager)
            ->get(route('portal.manager.team-attendance'));

        $response->assertStatus(200);
        $response->assertSee('Team Attendance');
    }

    /** @test */
    public function manager_can_approve_leave_requests()
    {
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $this->teamMember->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('portal.manager.approve-leave', $leaveRequest->id));

        $response->assertStatus(200);
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function manager_can_generate_team_reports()
    {
        $response = $this->actingAs($this->manager)
            ->get(route('portal.manager.team-report'));

        $response->assertStatus(200);
        $response->assertSee('Team Report');
    }
}
```

## 🚀 Implementation Roadmap

### **Week 1: Employee Portal**

1. Employee dashboard with personal metrics
2. Attendance tracking and clock in/out
3. Leave application system
4. Payslip access

### **Week 2: Manager Portal**

1. Team overview dashboard
2. Attendance approval workflow
3. Leave request management
4. Team reporting

### **Week 3: HR Admin Portal**

1. Employee management
2. Attendance oversight
3. Payroll processing interface
4. Analytics and reporting

### **Week 4: Kiosk & Mobile**

1. Attendance kiosk for physical locations
2. Mobile-responsive designs
3. Integration with biometric devices
4. Real-time notifications

These portals complete your HR ecosystem by providing dedicated interfaces for each user role with appropriate functionality and access levels. Ready to implement these with TDD? 🎯
