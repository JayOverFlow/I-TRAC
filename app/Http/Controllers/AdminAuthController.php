<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\MasterKey;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminAuthController extends Controller
{
    public function adminShowRegister (){
        if (session('is_admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.admin-register');
    }

    // Handle the registration of a new admin
    public function adminRegister(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                'unique:admins_tbl,admin_username',
                'regex:/^[a-zA-Z0-9_]+$/' // Only alphanumeric and underscore
            ],
            'master_key' => [
                'required',
                'string',
                'exists:master_keys_tbl,master_key'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed' // Requires password_confirmation field
            ],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'master_key.required' => 'Master key is required.',
            'master_key.exists' => 'Invalid master key.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Find the master key
        $masterKey = MasterKey::where('master_key', $request->master_key)->first();

        // Check if master key is already used
        if ($masterKey->isUsed()) {
            return back()->withErrors([
                'master_key' => 'This master key has already been used.'
            ])->withInput();
        }

        // Create a new admin with the validated data
        $admin = Admin::create([
            'admin_username' => $request->username,
            'admin_password' => Hash::make($request->password),
            'admin_key' => $masterKey->master_key_id,
        ]);

        // Redirect to admin login page after successful registration
        return redirect()->route('admin.show.login')->with('success', 'Admin account created successfully! You can now login.');
    }

    public function adminShowLogin (){
        if (session('is_admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.admin-login');
    }

    // Handle the admin login
    public function adminLogin(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'username' => [
                'required',
                'string'
            ],
            'password' => [
                'required',
                'string'
            ],
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        // Find the admin by username (Case-sensitive)
        $admin = Admin::whereRaw('BINARY admin_username = ?', [$request->username])->first();

        // Check if admin exists and password matches
        if (!$admin || !Hash::check($request->password, $admin->admin_password)) {
            return back()->withErrors([
                'username' => 'Invalid username or password.'
            ])->withInput();
        }

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        // Store admin session
        session([
            'admin_id' => $admin->admin_id,
            'admin_username' => $admin->admin_username,
            'is_admin_logged_in' => true
        ]);

        // Redirect to dashboard page
        return redirect()->route('admin.dashboard');
    }

    // // Admin welcome page
    // public function adminWelcome()
    // {
    //     return view('admin.welcome');
    // }

    // Admin logout
    public function adminLogout(Request $request)
    {
        // Clear admin session
        session()->forget(['admin_id', 'admin_username', 'is_admin_logged_in']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.show.login')->with('success', 'You have been logged out successfully.');
    }
}
