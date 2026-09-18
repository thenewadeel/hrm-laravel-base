<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Display the executive (Eagle Eye) dashboard.
     */
    public function index(): View|RedirectResponse
    {
        $organization = Organization::find(auth()->user()->current_organization_id);

        if (! $organization) {
            return redirect('/setup');
        }

        return view('dashboard', ['organization' => $organization]);
    }
}
