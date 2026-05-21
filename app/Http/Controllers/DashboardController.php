<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function showDashboard() {
        // Get the authenticated user
        $user = Auth::user();

        // Get the active user role dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;

        // Get necessary data to render
        $data = null;

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/head-dashboard', compact('data')),
            'Procurement' => view('procurement/pages/procurement-dashboard', compact('data')),
            'Supply'      => view('supply/pages/supply-dashboard', compact('data')),
            default       => view('errors.403'),
        };
    }
}
