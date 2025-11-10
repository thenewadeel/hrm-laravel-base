<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        return view('hr.employees.index');
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        return view('hr.employees.create');
    }

    /**
     * Display the specified employee.
     */
    public function show(User $employee)
    {
        return view('hr.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(User $employee)
    {
        return view('hr.employees.edit', compact('employee'));
    }
}
