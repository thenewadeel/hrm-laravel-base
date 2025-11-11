<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSlip extends Model
{
    /** @use HasFactory<\Database\Factories\PayrollSlipFactory> */
    use HasFactory, BelongsToOrganization;
}
