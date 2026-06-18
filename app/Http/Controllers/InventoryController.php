<?php

namespace App\Http\Controllers;

use App\Models\Mr;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function showInventory()
    {
        $user = Auth::user();
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();

        if (($activeRole?->gen_role) !== 'Supply') {
            return redirect()->route('404');
        }

        $mrItems = Mr::with(['assignedUser.departments'])->orderBy('date_scanned', 'desc')->get();

        $counts = [
            'all'             => $mrItems->count(),
            'equipment'       => $mrItems->where('category', 'Equipment')->count(),
            'semi_expendable' => $mrItems->where('category', 'Semi-Expendable')->count(),
            'supplies'        => $mrItems->where('category', 'Supply and Materials')->count(),
        ];

        return view('supply/pages/supply-inventory', compact('mrItems', 'counts'));
    }
}

