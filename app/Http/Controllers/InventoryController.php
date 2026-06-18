<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function showInventory() {
        // Get the authenticated user
        $user = Auth::user();

        // Resolve active role dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;

        // Get necessary data
        $mrItems = null;

        if ($userRole !== 'Supply') {
            return redirect()->route('404');
        }

        return view('supply/pages/supply-inventory', compact('mrItems'));
    }
}
