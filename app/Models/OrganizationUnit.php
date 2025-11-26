<?php

namespace App\Models;

use App\Models\Inventory\Store;
use App\Models\Organization;
use App\Models\Traits\BelongsToOrganization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationUnit extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;
    protected $casts = [
        'custom_fields' => 'array',
    ];

    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'organization_id',
        'custom_fields',
    ];

    public function parent()
    {
        return $this->belongsTo(OrganizationUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OrganizationUnit::class, 'parent_id')
            ->withTrashed();
    }
    public function users()
    {
        // CRITICAL FIX: Link to the custom pivot model
        return $this->belongsToMany(User::class, 'organization_user')
            ->using(OrganizationUser::class)
            ->withPivot(['position', 'roles', 'permissions']);
    }


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
    // ... (rest of the file is omitted for brevity)  // Recursive relationship for all descendants
    public function allDescendants()
    {
        return $this->hasMany(OrganizationUnit::class, 'parent_id')
            ->with('allDescendants');
    }

    public function allDescendantsWithSelf()
    {
        return OrganizationUnit::where(function ($query) {
            $query->where('id', $this->id)
                ->orWhereIn('id', $this->allDescendants->pluck('id'));
        });
    }

    // Recursive relationship for all ancestors
    public function allAncestors()
    {
        return $this->parent()->with('allAncestors');
    }

    // public function scopeWithDepth($query)
    // {
    //     $query->defaultOrder()->withDepthBelow(0);
    // }

    // public function scopeWithDepthBelow($query, $depth)
    // {
    //     $query->addSelect(['depth' => $query->query()->newQuery()
    //         ->selectRaw('count(parent.id)')
    //         ->from('organization_units as parent')
    //         ->whereColumn('parent.id', 'organization_units.parent_id')
    //         ->whereRaw('parent.parent_id = organization_units.id')])
    //         ->where('depth', '>=', $depth);
    // }

    // public function scopeDefaultOrder($query)
    // {
    //     $query->orderBy('depth')->orderBy('parent_id')->orderBy('name');
    // }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }
}
