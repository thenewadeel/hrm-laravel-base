<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRuns extends Model
{
    /** @use HasFactory<\Database\Factories\PayrollRunsFactory> */
    use HasFactory, BelongsToOrganization;
}
