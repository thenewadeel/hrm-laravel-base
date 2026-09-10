<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\PayrollRunsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRuns extends Model
{
    /** @use HasFactory<PayrollRunsFactory> */
    use BelongsToOrganization, HasFactory;
}
