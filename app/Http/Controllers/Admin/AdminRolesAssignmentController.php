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
            ->orderBy('r.role_name', 'ASC')       // Sort alphabetically
            ->get();

        $dashboardData['roles'] = $roles;

        // Fetch all users for the dropdowns
        $allUsers = User::orderBy('user_lastname', 'asc')
            ->orderBy('user_firstname', 'asc')
            ->get(['user_id', 'user_firstname', 'user_lastname', 'user_suffix']);

        $dashboardData['allUsers'] = $allUsers;

        return view('admin.pages.roles-assignment', $dashboardData);
    }

    public function updateRoleAssignments(Request $request)
    {
        // Expecting an array of assignments: [['role_id' => X, 'user_id' => Y], ...]
        $assignments = $request->input('assignments');

        if (!empty($assignments)) {
            DB::transaction(function () use ($assignments) {
                foreach ($assignments as $assignment) {
                    $roleId = $assignment['role_id'];
                    $userId = $assignment['user_id'];

                    if (empty($userId)) {
                        // Unassign: delete the specific role assignment
                        DB::table('user_roles_tbl')
                            ->where('role_id_fk', $roleId)
                            ->delete();
                    } else {
                        // Assign / Reassign
                        // First check if this role already has an assignment and update it, 
                        // or if not, insert a new one. Since a role in this context has one assigned user.
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

        // Table: users with their role and department via joins
        $users = DB::table('users as u')
            ->leftJoin('user_roles_tbl as ur', 'ur.user_id_fk', '=', 'u.user_id')
            ->leftJoin('roles_tbl as r', 'r.role_id', '=', 'ur.role_id_fk')
            ->leftJoin('departments_tbl as d', 'd.dep_id', '=', 'r.role_dep_id_fk')
            ->select(
                'u.user_tupid',
                'u.user_firstname',
                'u.user_lastname',
                'u.user_email',
                'r.role_name',
                'd.dep_name',
                'u.user_type'
            )
            ->get();

        return [
            'departments' => $departments,
            'officesCount' => $officesCount,
            'deptsCount' => $deptsCount,
            'facultyCount' => $facultyCount,
            'staffCount' => $staffCount,
            'users' => $users
        ];
    }
}
