<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppParent;
use App\Models\AppItem;
use App\Models\Task;
use App\Models\User;

class AssignPrController extends Controller
{
    public function showAssignPr($app_id)
    {
        $user = auth()->user();

        // Resolve active role & active department dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $dep_id = $activeRole ? $activeRole->role_dep_id_fk : null;

        // Fetch the APP data using app_id (with its items)
        $app_data = AppParent::with('appItems')->findOrFail($app_id);

        // Security Check: Ensure the APP actually belongs to the user's active department.
        // This prevents manual URL parameter tampering from letting users view APPs of other departments.
        if ($app_data->app_dep_id_fk !== $dep_id) {
            abort(403, 'Unauthorized access to this APP record.');
        }

        // Query users in the same department (excluding the authenticated user)
        // Subordinates are defined as all users who belong to the same department as the Head,
        // excluding any user that holds the 'Head' role in that specific department.
        $subordinates = collect();
        if ($dep_id) {
            $subordinates = User::whereHas('userDepartment', function ($q) use ($dep_id) {
                $q->where('department_id_fk', $dep_id);
            })
            ->whereDoesntHave('roles', function ($q) use ($dep_id) {
                // Exclude any user who holds a 'Head' role in this department
                $q->where('role_dep_id_fk', $dep_id)
                  ->where('gen_role', 'Head');
            })
            ->where('user_id', '!=', $user->user_id)
            ->get();
        }

        $breadcrumbs = [
            ['title' => 'Account Settings', 'url' => route('account.settings')],
            ['title' => 'Assign PR', 'url' => '']
        ];

        return view('head/pages/head-assign-pr', compact('app_data', 'subordinates', 'breadcrumbs'));
    }

    public function assignPr(Request $request) {
        $request->validate([
            'assigned_to' => 'required|integer|exists:users,user_id',
            'item_ids'    => 'required|array|min:1',
            'item_ids.*'  => 'integer|exists:app_items_tbl,app_item_id',
        ]);

        $headUserId  = auth()->user()->user_id;
        $assignedTo  = $request->assigned_to;
        $itemIds     = $request->item_ids;

        // Create ONE task for all selected items
        $task = Task::create([
            'assigned_by' => $headUserId,
            'assigned_to' => $assignedTo,
            'task_type'   => 'Purchase Request',
            'task_status' => 'Pending',
        ]);

        // Link the items to the task via pivot
        $task->appItems()->attach($itemIds);

        // Mark each selected item as assigned to this user
        AppItem::whereIn('app_item_id', $itemIds)->update(['app_items_assigned_to' => $assignedTo]);

        return response()->json(['success' => true]);
    }
    
}
