<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountSettingsController extends Controller
{
    public function showAccountSettings()
    {
        $user = Auth::user();

        $userRole = $user->roles()->first();

        if (!$userRole) {
            abort(403, 'Unassigned Role');
        }

        // Use switch statement to redirect user
        if ($userRole->gen_role === 'Head') {
            $apps = $user->appParents;
            return view('head.pages.head-account-settings', compact('user', 'apps'));
        }

        abort(404, 'Account settings not available for this role yet.');
    }
}
