<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminRolesOfficesController extends Controller
{
    public function index()
    {
        $data = $this->_getCommonData();

        // Fetch Roles and their Departments (Right join to ensure all Departments show, even if they have no Roles)
        $data['roles'] = DB::table('roles_tbl as r')
            ->rightJoin('departments_tbl as d', 'r.role_dep_id_fk', '=', 'd.dep_id')
            ->select('r.role_id', 'r.role_name', 'd.dep_name', 'd.dep_id', 'd.dep_type')
            ->get();

        return view('admin.pages.roles-offices', $data);
    }

    /**
     * Shared logic for card counts and departments list
     */
    private function _getCommonData()
    {
        return [
            'departments'  => Department::all(),
            'officesCount' => Department::where('dep_type', 'administrative')->count(),
            'deptsCount'   => Department::where('dep_type', 'academic')->count(),
            'facultyCount' => User::where('user_type', 'Faculty')->count(),
            'staffCount'   => User::where('user_type', 'Staff')->count(),
        ];
    }

    /**
     * Save new roles and potentially new departments
     */
    public function saveRoles(Request $request)
    {
        try {
            DB::beginTransaction();

            $newRoles = $request->input('new_roles', []);

            foreach ($newRoles as $roleData) {
                // Ignore entirely empty rows (should be caught by JS, but safe to check)
                if (empty($roleData['role_name']) && empty($roleData['new_department_name'])) {
                    continue;
                }

                $roleName = isset($roleData['role_name']) ? trim($roleData['role_name']) : '';
                $departmentId = $roleData['department_id'] ?? null;
                $newDeptName = isset($roleData['new_department_name']) ? trim($roleData['new_department_name']) : '';
                $newDeptType = isset($roleData['new_department_type']) ? trim($roleData['new_department_type']) : 'academic';

                // Create a new department if "NEW" was selected
                if ($departmentId === 'NEW' && !empty($newDeptName)) {
                    // Check if a department with this name already exists to prevent duplicate rows
                    $existingDept = Department::where('dep_name', $newDeptName)->first();

                    if ($existingDept) {
                        $departmentId = $existingDept->dep_id;
                    } else {
                        // Title-case the new department name
                        $formattedDeptName = collect(explode(' ', $newDeptName))->map(fn($word) => ucfirst(strtolower($word)))->join(' ');
                        
                        $newDept = Department::create([
                            'dep_name' => $formattedDeptName,
                            'dep_type' => $newDeptType
                        ]);
                        $departmentId = $newDept->dep_id;
                    }
                }

                // Create the role ONLY IF role name is intentionally provided
                if (!empty($roleName)) {
                    Role::create([
                        'role_name' => $roleName,
                        'role_dep_id_fk' => $departmentId,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Controller Error: ' . $e->getMessage() . ' on line ' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Delete a single role, and optionally its department
     */
    public function deleteRole(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $role = Role::find($id);

            if (!$role) {
                return response()->json(['success' => false, 'message' => 'Role not found.'], 404);
            }

            $departmentId = $role->role_dep_id_fk;

            // Delete the mapped role using the model primary key
            $role->delete();

            // If the user requested to delete the department as well
            if ($request->input('delete_department') === 'true' || $request->input('delete_department') === true) {
                
                // Ensure no OTHER roles are using this department before deleting it
                $remainingRoles = Role::where('role_dep_id_fk', $departmentId)->count();
                if ($remainingRoles > 0) {
                     DB::rollBack();
                     return response()->json(['success' => false, 'message' => 'Cannot delete department. It is still assigned to other roles.'], 400);
                }

                Department::where('dep_id', $departmentId)->delete();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete data. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete an empty department
     */
    public function deleteDepartment($id)
    {
        try {
            DB::beginTransaction();

            $department = Department::findOrFail($id);

            // Check if there are any roles still associated with this department
            $rolesCount = Role::where('role_dep_id_fk', $id)->count();

            if ($rolesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete department because it still has associated roles.'
                ], 400); // Bad Request
            }

            $department->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empty department deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateRole(Request $request, $id)
    {
        try {
            $request->validate([
                'role_name' => 'required|string|max:255',
                'department_id' => 'required|integer',
            ]);

            $role = Role::findOrFail($id);
            $role->role_name = $request->role_name;
            $role->role_dep_id_fk = $request->department_id;
            $role->save();

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateDepartment(Request $request, $id)
    {
        try {
            $request->validate([
                'dep_name' => 'required|string|max:255',
                'dep_type' => 'required|string|max:255',
            ]);

            $dept = Department::findOrFail($id);
            $dept->dep_name = collect(explode(' ', $request->dep_name))->map(fn($word) => ucfirst(strtolower($word)))->join(' ');
            $dept->dep_type = strtolower($request->dep_type);
            $dept->save();

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update department.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
