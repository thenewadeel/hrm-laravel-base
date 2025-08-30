<?php
// app/Http/Controllers/OrganizationController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        return view('organizations.index');
    }
}
