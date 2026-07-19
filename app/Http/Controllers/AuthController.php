<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


use App\Models\Department;
use App\Models\UserDepartment;

class AuthController extends Controller
{
    public function showLogin() {
        return view('auth/login');
    }

    public function login(Request $request) {
        // Valdations
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'regex:/^.+@tup\.edu\.ph$/i'],
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            // If email validation fails (required, format, or exists)
            if ($errors->has('email')) {
                return back()
                    ->withErrors(['all_fields' => 'Email or password is invalid.'])
                    ->withInput($request->only('email'));
            }

            // Fallback for other errors (like missing password)
            return back()
                ->withErrors(['all_fields' => 'All fields are required.'])
                ->withInput($request->only('email'));
        }

        // Get validated credentials
        $credentials = $validator->validated();

        // Authenticate login
        if (Auth::attempt(['user_email' => $credentials['email'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            // Get user gen_role
            $user = Auth::user();
            $firstRole = $user->roles->first();
            
            // Initialize active_role_id in session on first login fallback
            if ($firstRole) {
                session(['active_role_id' => $firstRole->role_id]);
            }
            
            $gen_role = $firstRole?->gen_role;

            switch ($gen_role) {
                case 'Head':
                case 'Procurement':
                case 'Supply':
                    return redirect()->route('show.dashboard');
                default:
                    return redirect()->route('show.mr');
            }
        }

        // Authentication failed
        return back()->withErrors([
            'auth_failed' => 'Email or password is invalid.'
        ])->withInput($request->only('email'));
    }

    public function showRegister() {
        // Fetch each division's parent and child departments/offices
        $directorOffice = Department::find(35);
        $directorChildren = Department::where('parent_dep_id', 35)
            ->whereNotIn('dep_id', [40, 38, 36])
            ->orderBy('dep_name', 'asc')
            ->get();

        $adafo = Department::find(40);
        $adafoChildren = Department::where('parent_dep_id', 40)
            ->orderBy('dep_name', 'asc')
            ->get();

        $adreo = Department::find(38);
        $adreoChildren = Department::where('parent_dep_id', 38)
            ->orderBy('dep_name', 'asc')
            ->get();

        $adaao = Department::find(36);
        $adaaoChildren = Department::where('parent_dep_id', 36)
            ->orderBy('dep_name', 'asc')
            ->get();

        $groupedDepartments = [
            [
                'parent' => $directorOffice,
                'children' => $directorChildren,
                'label' => "Director's Office & Direct Services"
            ],
            [
                'parent' => $adafo,
                'children' => $adafoChildren,
                'label' => "Assistant Director For Administration And Finance Office"
            ],
            [
                'parent' => $adreo,
                'children' => $adreoChildren,
                'label' => "Assistant Director for Research and Extension Office"
            ],
            [
                'parent' => $adaao,
                'children' => $adaaoChildren,
                'label' => "Assistant Director for Academic Affairs Office"
            ],
        ];

        return view('auth/register', compact('groupedDepartments'));
    }

    public function register(Request $request) {
        // Validate
        if ($request->has('validate_and_store')) {
            $step = $request->step;

            if ($step == 1) { // Step 1 validation
                $validator = Validator::make($request->all(), [
                    'first_name' => 'required|string|min:3|max:50',
                    'middle_name' => 'nullable|string|min:3|max:50',
                    'last_name' => 'required|string|min:3|max:50',
                    'suffix' => 'nullable|string|max:10',
                    'tup_id' => 'required|string|max:20|unique:users,user_tupid',
                ], [
                    'tup_id.unique' => 'TUPT-ID already exists.',
                ]);
            } elseif ($step == 2) { // Step 2 validation
                $validator = Validator::make($request->all(), [
                    'email' => 'required|string|regex:/^.+@tup\.edu\.ph$/i|unique:users,user_email',
                    'contact_no' => 'required|string|size:11|regex:/^09\d{9}$/',
                    'password' => 'required|string|min:8|max:128|regex:/^(?=.*[A-Za-z])(?=.*\d).{8,128}$/',
                    'confirm_password' => 'required|same:password',
                    'user_type' => 'required|in:Faculty,Staff',
                    'department' => 'required|integer|exists:departments_tbl,dep_id|min:1',
                ], [
                    'email.unique' => 'Email already exists. Please use a different email.',
                    'contact_no.required' => 'Contact number is required.',
                    'contact_no.size' => 'Contact number must be exactly 11 digits.',
                    'contact_no.regex' => 'Contact number must start with 09.',
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
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'tup_id',
                'email',
                'contact_no',
                'password',
                'user_type',
                'department'
            ]);
            if (isset($newData['tup_id'])) {
                $newData['tup_id'] = strtoupper($newData['tup_id']);
            }

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
                DB::beginTransaction();

                $user = \App\Models\User::create([
                    'user_firstname' => $registrationData['first_name'],
                    'user_middlename' => $registrationData['middle_name'],
                    'user_lastname' => $registrationData['last_name'],
                    'user_suffix' => $registrationData['suffix'],
                    'user_tupid' => $registrationData['tup_id'],
                    'user_email' => $registrationData['email'],
                    'user_contactno' => $registrationData['contact_no'],
                    'user_password' => $registrationData['password'],
                    'user_type' => $registrationData['user_type'],
                    'email_verified_at' => now(),
                ]);

                UserDepartment::create([
                    'user_id_fk' => $user->user_id,
                    'department_id_fk' => $registrationData['department'],
                ]);

                DB::commit();

                session()->forget('registration_data');

                return response()->json([
                    'success' => true,
                    'message' => 'Registration completed successfully! You can now log in.',
                    'user_id' => $user->user_id
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

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
        if (Auth::check()) {
            \Illuminate\Support\Facades\Cache::forget('last_seen_user_' . Auth::id());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Switch active role / department in session context.
     * Accessible by authenticated users to switch between their assigned department workflows.
     */
    public function switchAccount(Request $request)
    {
        // Validate that role_id is provided and exists in roles table
        $request->validate([
            'role_id' => 'required|integer|exists:roles_tbl,role_id',
        ]);

        $roleId = $request->input('role_id');
        $user = Auth::user();

        // Verify that the logged-in user actually owns this role
        $hasRole = $user->roles()->where('roles_tbl.role_id', $roleId)->exists();
        if (!$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized role switch.'
            ], 403);
        }

        // Store the selected role ID as active_role_id in the session
        session(['active_role_id' => $roleId]);

        return response()->json([
            'success' => true,
            'message' => 'Account switched successfully!'
        ]);
    }
}
