<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MrController extends Controller
{
    public function showMr() {
        // Get the authenticated user
        $user = Auth::user();

        // Resolve active role dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;

        // Get necessary data to render
        $data = null;

        // Redirect user based on role
        return match ($userRole) {
            'Head'             => view('head/pages/head-mr', compact('data')),
            null, 'Unassigned' => view('unassigned/pages/unassigned-mr', compact('data')), // Unassigned (No role) users
            'Procurement'      => view('procurement/pages/procurement-mr', compact('data')),
            'Supply'           => view('supply/pages/supply-mr', compact('data')),
            default            => abort(403),
        };
    }
}
