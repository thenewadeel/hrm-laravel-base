<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $dates = ['deleted_at'];

    public function users()
    {
        // CRITICAL FIX: Link to the custom pivot model
        return $this->belongsToMany(User::class, 'organization_user')
            ->using(OrganizationUser::class)
            ->withPivot(['roles', 'permissions', 'organization_unit_id', 'position'])
            ->withTimestamps();
    }
    public function units()
    {
        return $this->hasMany(OrganizationUnit::class);
    }
}
