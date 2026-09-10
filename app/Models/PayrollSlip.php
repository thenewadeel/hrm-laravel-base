<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\PayrollSlipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSlip extends Model
{
    /** @use HasFactory<PayrollSlipFactory> */
    use BelongsToOrganization, HasFactory;
}
