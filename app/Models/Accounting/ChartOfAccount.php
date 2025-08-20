<?php
// app/Models/Accounting/ChartOfAccount.php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'type', 'description'];

    public function scopeAssets($query)
    {
        return $query->where('type', 'asset');
    }

    public function scopeLiabilities($query)
    {
        return $query->where('type', 'liability');
    }

    public function scopeEquity($query)
    {
        return $query->where('type', 'equity');
    }

    public function scopeRevenues($query)
    {
        return $query->where('type', 'revenue');
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }
}
