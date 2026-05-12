<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRolesAssignmentController extends Controller
{
    // Admin Roles Assignment page
    public function index()
    {
        $dashboardData = $this->_getDashboardData();

        // Specific query to fetch all roles with their assigned user (if any)
        $roles = DB::table('roles_tbl as r')
            ->leftJoin('departments_tbl as d', 'd.dep_id', '=', 'r.role_dep_id_fk')
            ->leftJoin('user_roles_tbl as ur', 'ur.role_id_fk', '=', 'r.role_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'ur.user_id_fk')
            ->select(
                'r.role_id',
                'r.role_name',
                'd.dep_name',
                'u.user_id',
                'u.user_firstname',
                'u.user_lastname',
                'u.user_email'
            )
            ->orderByRaw('u.user_id IS NULL ASC') // Show non-null (assigned) users first
            ->orderBy('r.role_name', 'ASC')       // Then sort alphabetically by role name
            ->get();

        $dashboardData['roles'] = $roles;

        // Fetch all users for the dropdowns (roles view)
        $allUsers = User::orderBy('user_lastname', 'asc')
            ->orderBy('user_firstname', 'asc')
            ->get(['user_id', 'user_firstname', 'user_lastname', 'user_suffix']);

        $dashboardData['allUsers'] = $allUsers;

        // Fetch all roles with dep_id for dependent dropdown logic (users view)
        $allRoles = DB::table('roles_tbl as r')
            ->leftJoin('departments_tbl as d', 'd.dep_id', '=', 'r.role_dep_id_fk')
            ->select('r.role_id', 'r.role_name', 'r.role_dep_id_fk', 'd.dep_name')
            ->orderBy('r.role_name', 'ASC')
            ->get();

        $dashboardData['allRoles'] = $allRoles;

        return view('admin.pages.roles-assignment', $dashboardData);
    }

    /**
     * Update role assignments from the ROLES VIEW (role-centric).
     * Expects: [['role_id' => X, 'user_id' => Y], ...]
     */
    public function updateRoleAssignments(Request $request)
    {
        $assignments = $request->input('assignments');

        if (!empty($assignments)) {
            DB::transaction(function () use ($assignments) {
                foreach ($assignments as $assignment) {
                    $roleId = $assignment['role_id'];
                    $userId = $assignment['user_id'];

                    if (empty($userId)) {
                        DB::table('user_roles_tbl')
                            ->where('role_id_fk', $roleId)
                            ->delete();
                    } else {
                        DB::table('user_roles_tbl')->updateOrInsert(
                            ['role_id_fk' => $roleId],
                            ['user_id_fk' => $userId]
                        );
                    }
                }
            });
        }

        return response()->json(['success' => true, 'message' => 'Role assignments updated successfully.']);
    }

    /**
     * Update role assignments from the USERS VIEW (user-centric).
     * Expects: [['user_id' => X, 'role_id' => Y, 'dep_id' => Z, 'original_dep_id' => W], ...]
     */
    public function updateUserAssignments(Request $request)
    {
        $assignments = $request->input('assignments');

        if (!empty($assignments)) {
            DB::transaction(function () use ($assignments) {
                foreach ($assignments as $assignment) {
                    $userId = $assignment['user_id'];
                    $roleId = $assignment['role_id'];
                    $depId  = $assignment['dep_id'];
                    $origDepId = $assignment['original_dep_id'];

                    // 1. Handle Department Association Update
                    if (!empty($origDepId) && !empty($depId) && $origDepId != $depId) {
                        // Move user from one department to another
                        DB::table('user_departments_tbl')
                            ->where('user_id_fk', $userId)
                            ->where('department_id_fk', $origDepId)
                            ->update(['department_id_fk' => $depId]);
                    } elseif (empty($origDepId) && !empty($depId)) {
                        // New department association for the user
                        DB::table('user_departments_tbl')->updateOrInsert(
                            ['user_id_fk' => $userId, 'department_id_fk' => $depId],
                            []
                        );
                    }

                    // 2. Handle Role Assignment within this specific department
                    // We only manage roles that belong to the targeted department
                    $targetDepId = !empty($depId) ? $depId : $origDepId;

                    if ($targetDepId) {
                        // Find any existing role the user has in THIS department
                        $currentRole = DB::table('user_roles_tbl as ur')
                            ->join('roles_tbl as r', 'r.role_id', '=', 'ur.role_id_fk')
                            ->where('ur.user_id_fk', $userId)
                            ->where('r.role_dep_id_fk', $targetDepId)
                            ->select('ur.role_id_fk')
                            ->first();

                        if (empty($roleId)) {
                            // Unassign: remove role for this specific department
                            if ($currentRole) {
                                DB::table('user_roles_tbl')
                                    ->where('user_id_fk', $userId)
                                    ->where('role_id_fk', $currentRole->role_id_fk)
                                    ->delete();
                            }
                        } else {
                            // Assign / Update role for this specific department
                            if ($currentRole) {
                                if ($currentRole->role_id_fk != $roleId) {
                                    DB::table('user_roles_tbl')
                                        ->where('user_id_fk', $userId)
                                        ->where('role_id_fk', $currentRole->role_id_fk)
                                        ->update(['role_id_fk' => $roleId]);
                                }
                            } else {
                                // New role in this department
                                DB::table('user_roles_tbl')->insert([
                                    'user_id_fk' => $userId,
                                    'role_id_fk' => $roleId
                                ]);
                            }
                        }
                    }
                }
            });
        }

        return response()->json(['success' => true, 'message' => 'User assignments updated successfully.']);
    }

    /**
     * Get shared data for dashboard related pages
     * Copied from AdminDashboardController for isolation
     */
    private function _getDashboardData()
    {
        $departments = Department::orderBy('dep_name', 'asc')->get();

        // Card counts
        $officesCount = Department::where('dep_type', 'administrative')->count();
        $deptsCount   = Department::where('dep_type', 'academic')->count();
        $facultyCount = User::where('user_type', 'Faculty')->count();
        $staffCount   = User::where('user_type', 'Staff')->count();

        // For the "Add New User" dropdown in the assignment table
        $allUsers = User::orderBy('user_lastname', 'asc')
            ->orderBy('user_firstname', 'asc')
            ->get();

        $allRoles = DB::table('roles_tbl')->get();

        // Table: users with their role, role_id, dep_id, and department via joins
        // Inspired by view_user_roles_departments logic to support multiple departments per user
        $users = DB::table('users as u')
            ->leftJoin('user_roles_tbl as ur', 'u.user_id', '=', 'ur.user_id_fk')
            ->leftJoin('roles_tbl as r', 'ur.role_id_fk', '=', 'r.role_id')
            ->leftJoin('user_departments_tbl as ud', 'u.user_id', '=', 'ud.user_id_fk')
            ->leftJoin('departments_tbl as d', function($join) {
                $join->on('d.dep_id', '=', 'ud.department_id_fk')
                     ->orOn('d.dep_id', '=', 'r.role_dep_id_fk');
            })
            ->select(
                'u.user_id',
                'u.user_tupid',
                'u.user_firstname',
                'u.user_lastname',
                'u.user_email',
                'u.user_type',
                'd.dep_id',
                'd.dep_name',
                DB::raw("CASE WHEN r.role_dep_id_fk = d.dep_id THEN r.role_id ELSE NULL END as role_id"),
                DB::raw("CASE WHEN r.role_dep_id_fk = d.dep_id THEN r.role_name ELSE NULL END as role_name"),
                DB::raw("CASE WHEN r.role_dep_id_fk = d.dep_id THEN 0 ELSE 1 END as has_role")
            )
            ->distinct()
            ->orderBy('has_role', 'ASC')
            ->orderBy('u.user_lastname', 'ASC')
            ->orderBy('u.user_firstname', 'ASC')
            ->get();

        return [
            'departments'  => $departments,
            'officesCount' => $officesCount,
            'deptsCount'   => $deptsCount,
            'facultyCount' => $facultyCount,
            'staffCount'   => $staffCount,
            'users'        => $users,
            'allUsers'     => $allUsers,
            'allRoles'     => $allRoles
        ];
    }
}
