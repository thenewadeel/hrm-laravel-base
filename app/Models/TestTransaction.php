<?php
// app/Models/TestTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['amount'];
}
