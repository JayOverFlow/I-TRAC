<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MrController extends Controller
{
    public function showMr() {
        // Get the authenticated user
        $user = Auth::user();

        // Get the user role
        $userRole = $user->roles->first()?->gen_role;

        // Get necessary data to render
        $data = null;

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/head-mr', compact('data')),
            null          => view('unassigned/pages/unassigned-mr', compact('data')), // Unassinged (No role) users
            'ProcurementW'      => view('procurement/pages/procurement-mr', compact('data')),
            default       => view('errors.403'),
        };
    }
}
