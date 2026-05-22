<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminDashboardController extends Controller
{
    // Admin dashboard (Users)
    public function index()
    {
        return view('admin.pages.dashboard', $this->_getDashboardData());
    }

    /**
     * Update user profile information (General tab)
     */
    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'firstname' => 'required|string|min:3|max:50',
            'middlename' => 'nullable|string|min:3|max:50',
            'lastname' => 'required|string|min:3|max:50',
            'suffix' => 'nullable|string|max:10',
            'tupid' => 'required|string|max:6|regex:/^\d{6}$/|unique:users,user_tupid,' . $request->user_id . ',user_id',
            'contactno' => 'nullable|string|max:20',
        ], [
            'tupid.unique' => 'This TUPT ID is already taken by another user.',
            'tupid.regex' => 'TUPT ID must be exactly 6 digits.',
            'firstname.min' => 'First name must be at least 3 characters.',
            'lastname.min' => 'Last name must be at least 3 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        $user->update([
            'user_firstname' => $request->firstname,
            'user_middlename' => $request->middlename,
            'user_lastname' => $request->lastname,
            'user_suffix' => $request->suffix,
            'user_tupid' => $request->tupid,
            'user_contactno' => $request->contactno,
        ]);

        // Record Activity Log
        $userFullName = trim(implode(' ', array_filter([$user->user_firstname, $user->user_middlename, $user->user_lastname, $user->user_suffix])));
        ActivityLog::log(
            'USER_UPDATE',
            "Updated User: $userFullName",
            "Updated account profile details for user '$userFullName' (TUP ID: {$user->user_tupid})."
        );

        return response()->json([
            'success' => true,
            'message' => 'User profile updated successfully!'
        ]);
    }

    /**
     * Update/Reset user password under strict registration constraints (Security tab)
     */
    public function updateUserPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'new_password' => 'required|string|min:8|max:128|regex:/^(?=.*[A-Za-z])(?=.*\d).{8,128}$/',
        ], [
            'new_password.required' => 'Password is required.',
            'new_password.min' => 'Password must be at least 8 characters long.',
            'new_password.regex' => 'Password must contain at least one letter and one number.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        // Cast handles automatic hashing on model save
        $user->update([
            'user_password' => $request->new_password
        ]);

        // Record Activity Log
        $userFullName = trim(implode(' ', array_filter([$user->user_firstname, $user->user_middlename, $user->user_lastname, $user->user_suffix])));
        ActivityLog::log(
            'USER_PASSWORD_RESET',
            "Password Reset: $userFullName",
            "Administratively updated/reset the account password for user '$userFullName' (TUP ID: {$user->user_tupid})."
        );

        return response()->json([
            'success' => true,
            'message' => 'User password updated successfully!'
        ]);
    }

    /**
     * Delete user account securely after verifying admin credentials
     */
    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'admin_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid delete request payload.',
                'errors' => $validator->errors()
            ], 422);
        }

        $adminId = session('admin_id');
        $admin = \App\Models\Admin::find($adminId);

        if (!$admin || !Hash::check($request->admin_password, $admin->admin_password)) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization failed. Incorrect admin password.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = User::find($request->user_id);
            $userFullName = $user ? trim(implode(' ', array_filter([$user->user_firstname, $user->user_middlename, $user->user_lastname, $user->user_suffix]))) : 'Unknown User';
            $userTupId = $user ? $user->user_tupid : 'N/A';

            // Clear mappings in user_departments_tbl to prevent foreign key errors
            DB::table('user_departments_tbl')->where('user_id_fk', $request->user_id)->delete();

            // Delete the user record
            DB::table('users')->where('user_id', $request->user_id)->delete();

            // Record Activity Log
            ActivityLog::log(
                'USER_DELETE',
                "Deleted User: $userFullName",
                "Permanently deleted the account of user '$userFullName' (TUP ID: $userTupId) from the system."
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User account deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shared data for dashboard related pages
     */
    private function _getDashboardData()
    {
        $departments = Department::orderBy('dep_name', 'asc')->get();

        // Card counts
        $officesCount = Department::count();
        $programsCount = \App\Models\Role::where('role_name', 'like', 'Program Chair - %')->count();
        $facultyCount = User::where('user_type', 'Faculty')->count();
        $staffCount   = User::where('user_type', 'Staff')->count();

        // Table: users with their role and department via joins
        $rawUsers = DB::table('users as u')
            ->leftJoin('user_departments_tbl as ud', 'ud.user_id_fk', '=', 'u.user_id')
            ->leftJoin('roles_tbl as r', 'r.role_id', '=', 'ud.role_id_fk')
            ->leftJoin('departments_tbl as d', 'd.dep_id', '=', 'ud.department_id_fk')
            ->select(
                'u.user_id',
                'u.user_tupid',
                'u.user_firstname',
                'u.user_middlename',
                'u.user_lastname',
                'u.user_suffix',
                'u.user_email',
                'u.user_contactno',
                'u.user_profile_photo',
                'r.role_name',
                'd.dep_name',
                'u.user_type'
            )
            ->get();

        $grouped = [];
        foreach ($rawUsers as $row) {
            $userId = $row->user_id;
            if (!isset($grouped[$userId])) {
                $grouped[$userId] = (object)[
                    'user_id' => $row->user_id,
                    'user_tupid' => $row->user_tupid,
                    'user_firstname' => $row->user_firstname,
                    'user_middlename' => $row->user_middlename,
                    'user_lastname' => $row->user_lastname,
                    'user_suffix' => $row->user_suffix,
                    'user_email' => $row->user_email,
                    'user_contactno' => $row->user_contactno,
                    'user_profile_photo' => $row->user_profile_photo,
                    'user_type' => $row->user_type,
                    'roles' => [],
                    'departments' => []
                ];
            }
            if ($row->role_name && !in_array($row->role_name, $grouped[$userId]->roles)) {
                $grouped[$userId]->roles[] = $row->role_name;
            }
            if ($row->dep_name && !in_array($row->dep_name, $grouped[$userId]->departments)) {
                $grouped[$userId]->departments[] = $row->dep_name;
            }
        }
        $users = array_values($grouped);

        return [
            'departments' => $departments,
            'officesCount' => $officesCount,
            'programsCount' => $programsCount,
            'facultyCount' => $facultyCount,
            'staffCount' => $staffCount,
            'users' => $users
        ];
    }
}
