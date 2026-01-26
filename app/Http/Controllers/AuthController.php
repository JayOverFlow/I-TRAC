<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin() {
        return view('auth/login');
    }

    public function login(Request $request) {
        // Validate user credentials
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => ['required'],
        ]);

        // Authenticate credentials
        if(Auth::attempt($credentials)) {
            $request->session()->regenerate(); // Prevent session fixation for security
            return redirect()->intended('head/dashboard'); // Redirect to logged in page using ROUTE NAME
        }

        // Handle failed authentication
        return back()->withErrors([
            'msg' => 'The provided credentials do not match our records.',
        ]);
    }

    public function showRegister() {
        return view('auth/register');
    }

    public function register(Request $request) {
        // Validate user credentials
        

        // Authenticate credentials
        

        // Handle failed authentication
        
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('show.login');
    }
}
