<?php

// app/Http/Controllers/OrganizationController.php

namespace App\Http\Controllers;

class OrganizationController extends Controller
{
    public function index()
    {
        return view('organizations.index');
    }
}
