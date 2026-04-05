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
        // Fetch the APP data using app_id (with its items)
        $app_data = AppParent::with('appItems')->findOrFail($app_id);

        // Get the authenticated user's department via their first role
        $user = auth()->user();
        $dep_id = $user->roles()->first()?->role_dep_id_fk;

        // Query users in the same department (excluding the authenticated user)
        $subordinates = collect();
        if ($dep_id) {
            $subordinates = User::where(function ($query) use ($dep_id) {
                $query->whereHas('departments', function ($q) use ($dep_id) {
                    $q->where('department_id_fk', $dep_id);
                })->orWhereHas('roles', function ($q) use ($dep_id) {
                    $q->where('role_dep_id_fk', $dep_id);
                });
            })
                ->where('user_id', '!=', $user->user_id)
                ->get();
        }

        return view('head/pages/head-assign-pr', compact('app_data', 'subordinates'));
    }

    public function storeAssignPr(Request $request)
    {
        $request->validate([
            'assigned_to' => 'required|integer|exists:users,user_id',
            'item_ids'    => 'required|array|min:1',
            'item_ids.*'  => 'integer|exists:app_items_tbl,app_item_id',
        ]);

        $headUserId  = auth()->user()->user_id;
        $assignedTo  = $request->assigned_to;
        $itemIds     = $request->item_ids;

        // Create one task record per selected item
        foreach ($itemIds as $itemId) {
            Task::create([
                'assigned_by' => $headUserId,
                'assigned_to' => $assignedTo,
                'task_type'   => '',
                'task_status' => 'Pending',
            ]);
        }

        // Mark each selected item as assigned
        AppItem::whereIn('app_item_id', $itemIds)->update(['app_items_is_assigned' => 1]);

        return response()->json(['success' => true]);
    }
}
