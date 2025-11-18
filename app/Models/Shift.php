<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'start_time',
        'end_time',
        'days_of_week',
        'working_hours',
        'description',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'shift_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDurationAttribute()
    {
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);

        // Handle overnight shifts
        if ($end < $start) {
            $end += 24 * 3600; // Add 24 hours
        }

        return ($end - $start) / 3600.0; // Return hours as float
    }
}
