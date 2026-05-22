<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
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
            ->select('r.role_id', 'r.role_name', 'd.dep_name', 'd.dep_id', 'd.parent_dep_id')
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
            'officesCount' => Department::count(),
            'programsCount'   => Role::where('role_name', 'like', 'Program Chair - %')->count(),
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
                            'parent_dep_id' => 35
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

                    // Log the creation of a new role
                    $dept = Department::find($departmentId);
                    ActivityLog::log(
                        'ROLE_CREATE',
                        "New role: $roleName",
                        "Added new role '$roleName' under department '" . ($dept ? $dept->dep_name : 'Unknown') . "'"
                    );
                } else if ($departmentId !== 'NEW' && !empty($newDeptName)) {
                    // Log only department creation if no role was provided
                    ActivityLog::log(
                        'DEPT_CREATE',
                        "New Dept: $formattedDeptName",
                        "Added new department '$formattedDeptName' without any roles yet"
                    );
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

            $roleName = $role->role_name;
            $departmentId = $role->role_dep_id_fk; // Define departmentId from the role
            $dept = Department::find($departmentId);
            $deptName = $dept ? $dept->dep_name : 'Unknown';

            // Delete the mapped role using the model primary key
            $role->delete();

            // If the user requested to delete the department as well
            if ($request->input('delete_department') === 'true' || $request->input('delete_department') === true) {
                
                // Safeguard 1: Block deleting system structural pillars
                if (in_array($departmentId, [35, 36, 38, 40])) {
                     DB::rollBack();
                     return response()->json(['success' => false, 'message' => 'System Safeguard: Deleting this office is strictly prohibited.'], 400);
                }

                // Safeguard 2: Block deleting departments with sub-offices
                $subOfficesCount = Department::where('parent_dep_id', $departmentId)->count();
                if ($subOfficesCount > 0) {
                     DB::rollBack();
                     return response()->json(['success' => false, 'message' => 'System Safeguard: Cannot delete department because it still has sub-offices reporting to it. Reassign its sub-offices first.'], 400);
                }

                // Ensure no OTHER roles are using this department before deleting it
                $remainingRoles = Role::where('role_dep_id_fk', $departmentId)->count();
                if ($remainingRoles > 0) {
                     DB::rollBack();
                     return response()->json(['success' => false, 'message' => 'Cannot delete department. It is still assigned to other roles.'], 400);
                }

                Department::where('dep_id', $departmentId)->delete();

                ActivityLog::log(
                    'ROLE_DEPT_DELETE',
                    "Dept & Role deleted",
                    "Permanently deleted role '$roleName' and its parent department '$deptName'"
                );
            } else {
                ActivityLog::log(
                    'ROLE_DELETE',
                    "Role deleted: $roleName",
                    "Removed role '$roleName' from department '$deptName'"
                );
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

            // Safeguard 1: Block deleting system structural pillars
            if (in_array($id, [35, 36, 38, 40])) {
                return response()->json([
                    'success' => false,
                    'message' => 'System Safeguard: Deleting this office is strictly prohibited.'
                ], 400);
            }

            // Safeguard 2: Block deleting departments with sub-offices
            $subOfficesCount = Department::where('parent_dep_id', $id)->count();
            if ($subOfficesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'System Safeguard: Cannot delete department because it still has sub-offices reporting to it. Reassign its sub-offices first.'
                ], 400);
            }

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
            $oldName = $role->role_name;
            $oldDepId = $role->role_dep_id_fk;
            $oldDept = Department::find($oldDepId);

            $role->role_name = $request->role_name;
            $role->role_dep_id_fk = $request->department_id;
            $role->save();

            $newDept = Department::find($request->department_id);

            // Log if name changed
            if ($oldName !== $request->role_name) {
                ActivityLog::log(
                    'ROLE_RENAME',
                    "Renamed Role: {$request->role_name}",
                    "Updated role name from '$oldName' to '{$request->role_name}'"
                );
            }

            // Log if department changed (Reassignment)
            if ($oldDepId != $request->department_id) {
                ActivityLog::log(
                    'ROLE_REASSIGN',
                    "{$request->role_name} moved to " . ($newDept ? $newDept->dep_name : 'Unknown'),
                    "Reassigned role '{$request->role_name}' from '" . ($oldDept ? $oldDept->dep_name : 'Unknown') . "' to '" . ($newDept ? $newDept->dep_name : 'Unknown') . "'"
                );
            }

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
                'parent_dep_id' => 'nullable|integer|in:35,36,38,40',
            ]);

            $dept = Department::findOrFail($id);
            $oldName = $dept->dep_name;
            $oldParentId = $dept->parent_dep_id;

            $formattedName = collect(explode(' ', $request->dep_name))->map(fn($word) => ucfirst(strtolower($word)))->join(' ');
            
            $dept->dep_name = $formattedName;

            // Shield core pillars from being reassigned to any other parent
            if (!in_array((int)$id, [35, 36, 38, 40]) && $request->has('parent_dep_id')) {
                $newParentId = $request->parent_dep_id ? (int)$request->parent_dep_id : null;
                $dept->parent_dep_id = $newParentId;

                // Log Parent reassignment if changed
                if ($oldParentId != $newParentId) {
                    $oldParent = Department::find($oldParentId);
                    $newParent = Department::find($newParentId);
                    ActivityLog::log(
                        'DEPT_REASSIGN',
                        "{$formattedName} reassigned to " . ($newParent ? $newParent->dep_name : 'None'),
                        "Reassigned department '{$formattedName}' from '" . ($oldParent ? $oldParent->dep_name : 'None') . "' to '" . ($newParent ? $newParent->dep_name : 'None') . "'"
                    );
                }
            }

            $dept->save();

            // Log if name changed
            if ($oldName !== $formattedName) {
                ActivityLog::log(
                    'DEPT_RENAME',
                    "Renamed Dept: $formattedName",
                    "Updated department name from '$oldName' to '$formattedName'"
                );
            }

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
