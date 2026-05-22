<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
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
            ->leftJoin('user_departments_tbl as ud', 'ud.role_id_fk', '=', 'r.role_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'ud.user_id_fk')
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

        // Fetch all roles with dep_id and active assignments for dependent dropdown logic (users view)
        $allRoles = DB::table('roles_tbl as r')
            ->leftJoin('departments_tbl as d', 'd.dep_id', '=', 'r.role_dep_id_fk')
            ->leftJoin('user_departments_tbl as ud', 'ud.role_id_fk', '=', 'r.role_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'ud.user_id_fk')
            ->select(
                'r.role_id',
                'r.role_name',
                'r.role_dep_id_fk',
                'd.dep_name',
                'ud.user_id_fk as assigned_user_id',
                DB::raw("CONCAT(u.user_firstname, ' ', u.user_lastname) as assigned_user_name")
            )
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

                    $role = DB::table('roles_tbl')->where('role_id', $roleId)->first();
                    $roleName = $role ? $role->role_name : 'Unknown Role';

                    // Get OLD user for this role to track the change
                    $oldUserRole = DB::table('user_departments_tbl')->where('role_id_fk', $roleId)->first();
                    $oldUser = $oldUserRole ? User::find($oldUserRole->user_id_fk) : null;
                    $oldUserName = $oldUser ? "{$oldUser->user_firstname} {$oldUser->user_lastname}" : 'Vacant';

                    if (empty($userId)) {
                        DB::table('user_departments_tbl')
                            ->where('role_id_fk', $roleId)
                            ->update(['role_id_fk' => null]);
                        
                        if ($oldUser) {
                            ActivityLog::log(
                                'ROLE_UNASSIGN',
                                "Unassigned $oldUserName",
                                "Unassigned user $oldUserName from the role of '$roleName'"
                            );
                        }
                    } else {
                        // Clear the role from any previous user first (roles are 1-to-1)
                        DB::table('user_departments_tbl')
                            ->where('role_id_fk', $roleId)
                            ->update(['role_id_fk' => null]);

                        if ($role && $role->role_dep_id_fk) {
                            // Automatically associate the user with this role's parent department if not already present
                            DB::table('user_departments_tbl')->updateOrInsert(
                                [
                                    'user_id_fk' => $userId,
                                    'department_id_fk' => $role->role_dep_id_fk
                                ],
                                []
                            );

                            // Update the role_id_fk directly on their department row
                            DB::table('user_departments_tbl')
                                ->where('user_id_fk', $userId)
                                ->where('department_id_fk', $role->role_dep_id_fk)
                                ->update(['role_id_fk' => $roleId]);
                        }

                        $newUser = User::find($userId);
                        $newUserName = $newUser ? "{$newUser->user_firstname} {$newUser->user_lastname}" : 'Unknown';

                        if (!$oldUser || $oldUser->user_id != $userId) {
                            ActivityLog::log(
                                'ROLE_ASSIGN',
                                "$newUserName is now $roleName",
                                "Assigned $newUserName to '$roleName' (Previously: $oldUserName)"
                            );
                        }
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

                        $user = User::find($userId);
                        $userName = $user ? "{$user->user_firstname} {$user->user_lastname}" : 'Unknown';
                        $dept = Department::find($depId);
                        $deptName = $dept ? $dept->dep_name : 'Unknown';
                        $origDept = Department::find($origDepId);
                        $origDeptName = $origDept ? $origDept->dep_name : 'None';
                        $role = DB::table('roles_tbl')->where('role_id', $roleId)->first();
                        $roleName = $role ? $role->role_name : 'No Role';

                        // 1. Handle Department Association Update or Removal
                        if ($depId === 'REMOVE') {
                            if (!empty($origDepId)) {
                                // Safety check: ensure user has at least one other department
                                $count = DB::table('user_departments_tbl')->where('user_id_fk', $userId)->count();
                                if ($count > 1) {
                                    // Get current role name if any (for audit logs)
                                    $currentRole = DB::table('user_departments_tbl as ud')
                                        ->join('roles_tbl as r', 'r.role_id', '=', 'ud.role_id_fk')
                                        ->where('ud.user_id_fk', $userId)
                                        ->where('ud.department_id_fk', $origDepId)
                                        ->select('r.role_name')
                                        ->first();

                                    // Delete Department Association
                                    DB::table('user_departments_tbl')
                                        ->where('user_id_fk', $userId)
                                        ->where('department_id_fk', $origDepId)
                                        ->delete();

                                    ActivityLog::log(
                                        'DEPT_REMOVED',
                                        "$userName removed from $origDeptName",
                                        "Removed $userName from department: $origDeptName and cleared associated role: " . ($currentRole ? $currentRole->role_name : 'None')
                                    );
                                }
                            }
                            continue; // Skip further role logic for this row
                        }

                        if (!empty($origDepId) && !empty($depId) && $origDepId != $depId) {
                            // Move user from one department to another in user_departments_tbl and clear their role
                            DB::table('user_departments_tbl')
                                ->where('user_id_fk', $userId)
                                ->where('department_id_fk', $origDepId)
                                ->update([
                                    'department_id_fk' => $depId,
                                    'role_id_fk' => null
                                ]);

                            ActivityLog::log(
                                'DEPT_TRANSFER',
                                "$userName moved to $deptName",
                                "Transferred $userName from $origDeptName to $deptName as 'No Role'"
                            );
                        } elseif (empty($origDepId) && !empty($depId)) {
                            // New department association for the user
                            DB::table('user_departments_tbl')->updateOrInsert(
                                ['user_id_fk' => $userId, 'department_id_fk' => $depId],
                                ['role_id_fk' => null]
                            );

                            ActivityLog::log(
                                'DEPT_ADD',
                                "$userName added to $deptName",
                                "Added $userName to additional department: '$deptName' as 'No Role'"
                            );
                        }

                        // 2. Handle Role Assignment within this specific department
                        $targetDepId = !empty($depId) ? $depId : $origDepId;

                        if ($targetDepId) {
                            $currentRoleEntry = DB::table('user_departments_tbl as ud')
                                ->join('roles_tbl as r', 'r.role_id', '=', 'ud.role_id_fk')
                                ->where('ud.user_id_fk', $userId)
                                ->where('ud.department_id_fk', $targetDepId)
                                ->select('ud.role_id_fk', 'r.role_name')
                                ->first();

                            if (empty($roleId)) {
                                if ($currentRoleEntry) {
                                    DB::table('user_departments_tbl')
                                        ->where('user_id_fk', $userId)
                                        ->where('department_id_fk', $targetDepId)
                                        ->update(['role_id_fk' => null]);
                                    
                                    ActivityLog::log(
                                        'ROLE_REMOVED',
                                        "Role removed: {$currentRoleEntry->role_name}",
                                        "Removed $userName from the role of '{$currentRoleEntry->role_name}' in $deptName"
                                    );
                                }
                            } else {
                                // Clear this role from any previous user first (roles are 1-to-1)
                                DB::table('user_departments_tbl')
                                    ->where('role_id_fk', $roleId)
                                    ->update(['role_id_fk' => null]);

                                if ($currentRoleEntry) {
                                    if ($currentRoleEntry->role_id_fk != $roleId) {
                                        DB::table('user_departments_tbl')
                                            ->where('user_id_fk', $userId)
                                            ->where('department_id_fk', $targetDepId)
                                            ->update(['role_id_fk' => $roleId]);

                                        ActivityLog::log(
                                            'ROLE_CHANGE',
                                            "$userName updated in $deptName",
                                            "Changed $userName's role in $deptName from '{$currentRoleEntry->role_name}' to '$roleName'"
                                        );
                                    }
                                } else {
                                    // Assign the role directly on their department record
                                    DB::table('user_departments_tbl')
                                        ->where('user_id_fk', $userId)
                                        ->where('department_id_fk', $targetDepId)
                                        ->update(['role_id_fk' => $roleId]);

                                    ActivityLog::log(
                                        'ROLE_ASSIGN',
                                        "$userName is now $roleName",
                                        "Assigned $userName to '$roleName' in $deptName"
                                    );
                                }
                            }
                        }
                }
            });
        }

        return response()->json(['success' => true, 'message' => 'User assignments updated successfully.']);
    }

    /**
     * Delete a specific user-department assignment immediately.
     */
    public function deleteUserDepartment(Request $request)
    {
        $userId = $request->input('user_id');
        $depId  = $request->input('dep_id');

        if (empty($userId) || empty($depId)) {
            return response()->json(['success' => false, 'message' => 'Missing user or department identifier.'], 400);
        }

        // Safety check: ensure user has at least one other department remaining
        $count = DB::table('user_departments_tbl')->where('user_id_fk', $userId)->count();
        if ($count <= 1) {
            return response()->json(['success' => false, 'message' => 'Safety constraint: A user must belong to at least one department.'], 400);
        }

        $user = User::find($userId);
        $userName = $user ? "{$user->user_firstname} {$user->user_lastname}" : 'Unknown User';
        $dept = Department::find($depId);
        $deptName = $dept ? $dept->dep_name : 'Unknown Department';

        // Find active role for this user/department assignment to include in audit logs
        $currentRole = DB::table('user_departments_tbl as ud')
            ->leftJoin('roles_tbl as r', 'r.role_id', '=', 'ud.role_id_fk')
            ->where('ud.user_id_fk', $userId)
            ->where('ud.department_id_fk', $depId)
            ->select('r.role_name')
            ->first();

        // Delete department association
        DB::table('user_departments_tbl')
            ->where('user_id_fk', $userId)
            ->where('department_id_fk', $depId)
            ->delete();

        // Log activity
        ActivityLog::log(
            'DEPT_REMOVED',
            "$userName removed from $deptName",
            "Removed $userName from department: $deptName and cleared associated role: " . ($currentRole && $currentRole->role_name ? $currentRole->role_name : 'None')
        );

        return response()->json(['success' => true, 'message' => 'User successfully removed from department.']);
    }

    /**
     * Get shared data for dashboard related pages
     * Copied from AdminDashboardController for isolation
     */
    private function _getDashboardData()
    {
        $departments = Department::orderBy('dep_name', 'asc')->get();

        // Card counts
        $officesCount = Department::count();
        $programsCount = Role::where('role_name', 'like', 'Program Chair - %')->count();
        $facultyCount = User::where('user_type', 'Faculty')->count();
        $staffCount   = User::where('user_type', 'Staff')->count();

        // For the "Add New User" dropdown in the assignment table
        $allUsers = User::orderBy('user_lastname', 'asc')
            ->orderBy('user_firstname', 'asc')
            ->get();

        $allRoles = DB::table('roles_tbl as r')
            ->leftJoin('departments_tbl as d', 'd.dep_id', '=', 'r.role_dep_id_fk')
            ->leftJoin('user_departments_tbl as ud', 'ud.role_id_fk', '=', 'r.role_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'ud.user_id_fk')
            ->select(
                'r.role_id',
                'r.role_name',
                'r.role_dep_id_fk',
                'd.dep_name',
                'ud.user_id_fk as assigned_user_id',
                DB::raw("CONCAT(u.user_firstname, ' ', u.user_lastname) as assigned_user_name")
            )
            ->orderBy('r.role_name', 'ASC')
            ->get();

        // Table: users with their role, role_id, dep_id, and department via joins
        // Refactored to use groupBy to prevent Cartesian product (duplicate rows)
        // when a user belongs to multiple departments and has multiple roles.
        $users = DB::table('users as u')
            ->leftJoin('user_departments_tbl as ud', 'u.user_id', '=', 'ud.user_id_fk')
            ->leftJoin('departments_tbl as d', 'd.dep_id', '=', 'ud.department_id_fk')
            ->leftJoin('roles_tbl as r', 'r.role_id', '=', 'ud.role_id_fk')
            ->select(
                'u.user_id',
                'u.user_tupid',
                'u.user_firstname',
                'u.user_lastname',
                'u.user_email',
                'u.user_type',
                'd.dep_id',
                'd.dep_name',
                DB::raw('(SELECT COUNT(*) FROM user_departments_tbl WHERE user_id_fk = u.user_id) as dep_count'),
                DB::raw('MAX(r.role_id) as role_id'),
                DB::raw('MAX(r.role_name) as role_name'),
                DB::raw("CASE WHEN MAX(r.role_id) IS NOT NULL THEN 0 ELSE 1 END as has_role")
            )
            ->groupBy(
                'u.user_id',
                'u.user_tupid',
                'u.user_firstname',
                'u.user_lastname',
                'u.user_email',
                'u.user_type',
                'd.dep_id',
                'd.dep_name',
                'dep_count'
            )
            ->orderBy('has_role', 'ASC')
            ->orderBy('u.user_lastname', 'ASC')
            ->orderBy('u.user_firstname', 'ASC')
            ->get();

        return [
            'departments'  => $departments,
            'officesCount' => $officesCount,
            'programsCount'   => $programsCount,
            'facultyCount' => $facultyCount,
            'staffCount'   => $staffCount,
            'users'        => $users,
            'allUsers'     => $allUsers,
            'allRoles'     => $allRoles
        ];
    }
}
