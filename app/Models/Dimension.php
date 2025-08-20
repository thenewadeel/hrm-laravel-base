<?php
// app/Models/Dimension.php

namespace App\Models;

use App\Models\Accounting\LedgerEntry; // <-- Add this import
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Dimension extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'type', 'description'];

    public function ledgerEntries(): MorphToMany
    {
        return $this->morphedByMany(LedgerEntry::class, 'dimensionable');
    }
}
