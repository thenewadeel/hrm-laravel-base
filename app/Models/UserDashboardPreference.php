<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores an individual user's custom Executive Dashboard layout for an organization.
 */
class UserDashboardPreference extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'layout',
    ];

    /**
     * The JSON layout (ordered widget keys) is handled as an array.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'layout' => 'array',
        ];
    }

    /**
     * The user this preference belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The organization this preference is scoped to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
