<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'is_active'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the users that belong to the organization.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['roles', 'permissions'])
            ->withTimestamps();
    }
    public function units()
    {
        return $this->hasMany(OrganizationUnit::class);
    }
}
