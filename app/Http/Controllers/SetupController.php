<?php
// app/Http/Controllers/SetupController.php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\Inventory\Store;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function storeOrganization(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3|max:255|unique:organizations,name',
        ]);

        DB::transaction(function () use ($validated) {
            // Create organization
            $organization = Organization::create([
                'name' => $validated['name'],
                'description' => 'Organization created during setup',
                'is_active' => true,
            ]);

            // Create root organization unit
            $rootUnit = OrganizationUnit::create([
                'name' => 'Head Office',
                'type' => 'head_office',
                'organization_id' => $organization->id,
                'parent_id' => null,
            ]);

            // Attach user to organization with admin role
            auth()->user()->organizations()->attach($organization->id, [
                'roles' => json_encode(['admin']),
                'organization_unit_id' => $rootUnit->id,
                'position' => 'Administrator'
            ]);
        });

        return redirect('/setup/stores');
    }

    public function storeStore(Request $request)
    {
        $user = auth()->user();
        $organization = $user->organizations()->first();

        $validated = $request->validate([
            'name' => 'required|min:3|max:255',
            'location' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:inventory_stores,code',
        ]);

        DB::transaction(function () use ($validated, $organization) {
            // Get or create root organization unit
            $rootUnit = $organization->units()->where('name', 'Head Office')->first();

            if (!$rootUnit) {
                $rootUnit = OrganizationUnit::create([
                    'name' => 'Head Office',
                    'type' => 'head_office',
                    'organization_id' => $organization->id,
                    'parent_id' => null,
                ]);
            }

            // Auto-generate code if not provided
            $code = $validated['code'] ?? 'STORE' . str_pad((Store::forOrganization($organization->id)->count() +  1), 3, '0', STR_PAD_LEFT);

            // Create store
            Store::create([
                'name' => $validated['name'],
                'location' => $validated['location'] ?? 'Head Office',
                'code' => $code,
                'organization_unit_id' => $rootUnit->id,
                'organization_id' => $organization->id,
                'is_active' => true,
            ]);
        });

        return redirect('/setup/accounts');
    }

    // app/Http/Controllers/SetupController.php - Fix the storeAccounts method

    public function storeAccounts(Request $request)
    {
        $user = auth()->user();
        $organization = $user->organizations()->first();

        $validated = $request->validate([
            'setup_default_accounts' => 'required|boolean',
        ]);

        DB::transaction(function () use ($validated, $organization) {
            if ($validated['setup_default_accounts']) {
                $this->createDefaultChartOfAccounts($organization);
            }

            // Create accounting department organization unit
            $headOffice = $organization->units()->where('name', 'Head Office')->first();

            // if ($headOffice) {
            OrganizationUnit::create([
                'name' => 'Accounting Department',
                'type' => 'department',
                'organization_id' => $organization->id,
                'parent_id' => $headOffice ? $headOffice->id : null,
            ]);
            // }
        });

        return redirect('/dashboard')->with('success', 'Setup completed successfully!');
    }

    protected function createDefaultChartOfAccounts($organization)
    {
        $defaultAccounts = [
            // Assets
            ['code' => '1001', 'name' => 'Cash', 'type' => 'asset', 'description' => 'Cash on hand and in bank'],
            ['code' => '1002', 'name' => 'Accounts Receivable', 'type' => 'asset', 'description' => 'Amounts owed by customers'],
            ['code' => '1003', 'name' => 'Inventory', 'type' => 'asset', 'description' => 'Goods available for sale'],
            ['code' => '1004', 'name' => 'Equipment', 'type' => 'asset', 'description' => 'Office and operational equipment'],

            // Liabilities
            ['code' => '2001', 'name' => 'Accounts Payable', 'type' => 'liability', 'description' => 'Amounts owed to suppliers'],
            ['code' => '2002', 'name' => 'Loans Payable', 'type' => 'liability', 'description' => 'Outstanding loans'],

            // Equity
            ['code' => '3001', 'name' => 'Owner\'s Equity', 'type' => 'equity', 'description' => 'Owner investment in the business'],
            ['code' => '3002', 'name' => 'Retained Earnings', 'type' => 'equity', 'description' => 'Accumulated profits'],

            // Revenue
            ['code' => '4001', 'name' => 'Sales Revenue', 'type' => 'revenue', 'description' => 'Revenue from product sales'],
            ['code' => '4002', 'name' => 'Service Revenue', 'type' => 'revenue', 'description' => 'Revenue from services'],

            // Expenses
            ['code' => '5001', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'description' => 'Cost of inventory sold'],
            ['code' => '5002', 'name' => 'Rent Expense', 'type' => 'expense', 'description' => 'Office and store rent'],
            ['code' => '5003', 'name' => 'Salary Expense', 'type' => 'expense', 'description' => 'Employee salaries and wages'],
            ['code' => '5004', 'name' => 'Utilities Expense', 'type' => 'expense', 'description' => 'Electricity, water, internet'],
        ];

        foreach ($defaultAccounts as $account) {
            ChartOfAccount::create(array_merge($account, [
                'organization_id' => $organization->id,
            ]));
        }
    }
}
