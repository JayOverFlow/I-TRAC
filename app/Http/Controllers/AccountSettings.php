<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountSettings extends Controller
{
    public function showAccountSettings () {
        // 1. Check user has role or unassigned
        // 2. Fetch user data needed to render (Profile, Inbox, Settings, and APP (for Head))
        // 3. Conditional statement to redirect the user based on the role or not
        // 4. Return the view with the user data
        
    }
}
