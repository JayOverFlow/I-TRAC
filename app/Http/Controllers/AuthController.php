<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Department;

class AuthController extends Controller
{
    public function showLogin() {
        return view('auth/login');
    }

    public function login(Request $request) {
        // Valdations
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|exists:users,user_email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            // Authentication failed
            return back()
                ->withErrors(['all_fields' => 'All fields are required.'])
                ->withInput($request->only('email'));
        }

        // Get validated credentials
        $credentials = $validator->validated();

        // Authenticate login
        if (Auth::attempt(['user_email' => $credentials['email'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            return redirect()->route('show.dashboard');
        }

        // Authentication failed
        return back()->withErrors([
            'auth_failed' => 'Email or password is invalid.'
        ])->withInput($request->only('email'));
        
    }

    public function showRegister() {
        // Get the departments in the db for dropdown
        $departments = Department::orderBy('dep_name', 'asc')->get();

        return view('auth/register', compact('departments'));
    }

    public function register(Request $request) {
        // Validate
        if ($request->has('validate_and_store')) {
            $step = $request->step;
 
            if ($step == 1) { // Step 1 validation
                $validator = Validator::make($request->all(), [
                    'first_name' => 'required|string|min:3|max:50',
                    'middle_name' => 'required|string|min:3|max:50',
                    'last_name' => 'required|string|min:3|max:50',
                    'suffix' => 'nullable|string|max:10',
                    'tup_id' => 'required|string|max:6|unique:users,user_tupid|regex:/^\d{6}$/',
                ],[
                    'tup_id.unique' => 'TUP ID already exists.',
                ]);
            } elseif ($step == 2) { // Step 2 validation
                $validator = Validator::make($request->all(), [
                    'email' => 'required|string|regex:/^.+@tup\.edu\.ph$/i|unique:users,user_email',
                    'password' => 'required|string|min:8|max:128|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/',
                    'confirm_password' => 'required|same:password',
                    'user_type' => 'required|in:Faculty,Staff',
                    'department' => 'required|integer|exists:departments_tbl,dep_id|min:1',
                ], [
                    'email.unique' => 'Email already exists. Please use a different email.',
                    'password.regex' => 'Password must contain at least one letter and one number.',
                    'confirm_password.same' => 'Passwords do not match.',
                    'user_type.required' => 'Please select a user type.',
                    'user_type.in' => 'Please select a valid user type.',
                    'department.exists' => 'Select a valid department.',
                    'department.min' => 'Please select a department.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid step'
                ], 400);
            }

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Store validated data in session
            $currentData = session('registration_data', []);
            $newData = $request->only([
                'first_name', 'middle_name', 'last_name', 'suffix',
                'tup_id', 'email', 'password', 'user_type', 'department'
            ]);

            session(['registration_data' => array_merge($currentData, $newData)]);

            return response()->json([
                'success' => true,
                'message' => 'Step ' . $step . ' validated and saved successfully'
            ]);
        }

        // Handle registration completion after email verification
        if ($request->has('complete_registration') && $request->complete_registration) {
            $registrationData = session('registration_data');
            
            if (!$registrationData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration data not found. Please start over.'
                ], 400);
            }
            
            try {
                $user = \App\Models\User::create([
                    'user_firstname' => $registrationData['first_name'],
                    'user_middlename' => $registrationData['middle_name'],
                    'user_lastname' => $registrationData['last_name'],
                    'user_suffix' => $registrationData['suffix'],
                    'user_tupid' => $registrationData['tup_id'],
                    'user_email' => $registrationData['email'],
                    'user_password' => bcrypt($registrationData['password']),
                    'user_type' => $registrationData['user_type'],
                    'email_verified_at' => now(),
                ]);
                
                session()->forget('registration_data');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Registration completed successfully! You can now log in.',
                    'user_id' => $user->user_id
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid request'
        ], 400);
        
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('show.login');
    }
}
