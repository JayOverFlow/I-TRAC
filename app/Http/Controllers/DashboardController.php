<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function showDashboard() {
        // Get the authenticated user
        $user = Auth::user();

        // Get the user role
        $userRole = $user->roles->first()?->gen_role;

        // Get necessary data to render
        $data = null;

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/dashboard', compact('data')),
            // 'Procurement' => view('procurement.dashboard'),
            // 'Supply'      => view('supply.dashboard'),
            default       => view('errors.403'),
        };
    }
}
