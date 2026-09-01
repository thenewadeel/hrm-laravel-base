<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosition extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'organization_unit_id',
        'title',
        'code',
        'description',
        'min_salary',
        'max_salary',
        'requirements',
        'default_roles',
        'is_active',
    ];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('title', 'LIKE', "%{$term}%")
            ->orWhere('code', 'LIKE', "%{$term}%")
            ->orWhere('description', 'LIKE', "%{$term}%");
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [

            'requirements' => 'array',
            'default_roles' => 'array',
            'min_salary' => 'decimal:2',
            'max_salary' => 'decimal:2',
            'is_active' => 'boolean',

        ];
    }

    /**
     * All roles available in the system, usable as position default roles.
     *
     * @return array<string, string>
     */
    public static function roleOptions(): array
    {
        return [
            'admin' => 'Super Admin',
            'organization_admin' => 'Organization Admin',
            'organization_unit_manager' => 'Unit Manager',
            'user_manager' => 'User Manager',
            'inventory_admin' => 'Inventory Admin',
            'store_manager' => 'Store Manager',
            'inventory_clerk' => 'Inventory Clerk',
            'accounting_manager' => 'Accounting Manager',
            'senior_accountant' => 'Senior Accountant',
            'accountant' => 'Accountant',
            'ap_clerk' => 'Accounts Payable Clerk',
            'ar_clerk' => 'Accounts Receivable Clerk',
            'budget_analyst' => 'Budget Analyst',
            'financial_approver' => 'Financial Approver',
            'membership.admin' => 'Membership Admin',
            'membership.manager' => 'Membership Manager',
            'membership.clerk' => 'Membership Clerk',
            'membership.viewer' => 'Membership Viewer',
            'auditor' => 'Auditor',
            'employee' => 'Employee',
            'hr' => 'HR Manager',
            'manager' => 'Manager',
        ];
    }
}
