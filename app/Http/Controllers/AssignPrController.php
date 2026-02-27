<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppParent;
use App\Models\User;

class AssignPrController extends Controller
{
    public function showAssignPr ($app_id) {
        // Fetch the APP data using app_id
        $app_data = AppParent::with('appItems')->findOrFail($app_id);

        // Get the authenticated user's department via their role
        $user = auth()->user();
        $dep_id = $user->roles()->first()?->role_dep_id_fk;

        // Query users in the same department (excluding the authenticated user)
        $subordinates = collect();
        if ($dep_id) {
            $subordinates = User::whereHas('departments', function ($q) use ($dep_id) {
                $q->where('department_id_fk', $dep_id);
            })->where('user_id', '!=', $user->user_id)->get();
        }

        return view('head/pages/head-assign-pr', compact('app_data', 'subordinates'));
    }
}