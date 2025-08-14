<?php

// OrganizationUnit.php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OrganizationUnit extends Model
{
    protected $casts = [
        'custom_fields' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent()
    {
        return $this->belongsTo(OrganizationUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OrganizationUnit::class, 'parent_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['position', 'permissions', 'is_active']);
    }

    // Recursive relationship for all descendants
    public function allDescendants()
    {
        return $this->children()->with('allDescendants');
    }

    // Recursive relationship for all ancestors
    public function allAncestors()
    {
        return $this->parent()->with('allAncestors');
    }
}
