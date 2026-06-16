<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function showTasks() {
        // Get the authenticated user
        $user = Auth::user();

        // Resolve active role and active department dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;
        $depId = $activeRole ? $activeRole->role_dep_id_fk : null;

        $isHeadRole = in_array($userRole, ['Head', 'Procurement', 'Supply']);

        // Retrieve active APP and calculate budget dynamically
        $activeAppId = $depId ? session('active_app_id_' . $depId) : null;
        
        // Fetch active APP of this department from database if none set in session
        if (!$activeAppId && $depId) {
            $dbActiveApp = \App\Models\AppParent::where('app_dep_id_fk', $depId)
                ->where('is_active', true)
                ->first();
            if ($dbActiveApp) {
                $activeAppId = $dbActiveApp->app_id;
                session(['active_app_id_' . $depId => $activeAppId]);
            }
        }

        $activeApp = $activeAppId ? \App\Models\AppParent::with('appItems')->find($activeAppId) : null;

        $departmentBudget = $activeApp ? $activeApp->app_total : 0;
        $activeAppItems = $activeApp ? $activeApp->appItems : collect();

        // Build the query to fetch tasks assigned to the logged-in user or related to their department
        if ($isHeadRole && $depId) {
            // For Head, Procurement, and Supply roles, view all tasks in their department
            $tasksQuery = \App\Models\Task::with(['assignedBy', 'assignedTo', 'purchaseRequest', 'appItems'])
                ->where('is_deleted', 0)
                ->where('task_dep_id_fk', $depId);
        } else {
            // For standard/unassigned users, only show tasks assigned to them, scoped to their active department
            $tasksQuery = \App\Models\Task::with(['assignedBy', 'assignedTo', 'purchaseRequest', 'appItems'])
                ->where('is_deleted', 0)
                ->where('assigned_to', $user->user_id);

            if ($depId) {
                $tasksQuery->where('task_dep_id_fk', $depId);
            }
        }

        // Scope tasks to strictly belong to the active APP for Head/Procurement/Supply roles.
        // For subordinate users (non-Head), they should see all PR tasks assigned to them regardless of active APP.
        if ($isHeadRole) {
            if ($activeAppId) {
                $tasksQuery->whereHas('appItems', function ($q) use ($activeAppId) {
                    $q->where('app_id_fk', $activeAppId);
                });
            } else {
                $tasksQuery->whereRaw('1 = 0');
            }
        }

        $tasks = $tasksQuery->orderByDesc('created_at')->get();

        // Calculate estimated budget for each task
        foreach ($tasks as $task) {
            $task->estimated_budget = $task->appItems->sum('app_items_esti_budget') ?? 0;
        }

        // Collect app_item_ids already attached to existing non-deleted tasks
        $usedAppItemIds = collect();
        if ($activeAppItems->isNotEmpty()) {
            $allItemIds = $activeAppItems->pluck('app_item_id')->toArray();
            $usedAppItemIds = \Illuminate\Support\Facades\DB::table('task_items_tbl')
                ->join('tasks_tbl', 'task_items_tbl.task_id_fk', '=', 'tasks_tbl.task_id')
                ->whereIn('task_items_tbl.app_item_id_fk', $allItemIds)
                ->where('tasks_tbl.is_deleted', 0)
                ->pluck('task_items_tbl.app_item_id_fk');
        }

        // Fetch subordinates of the active department context (excluding heads/procurement/supply)
        $subordinates = collect();
        if ($isHeadRole && $depId) {
            $subordinates = \App\Models\User::whereHas('userDepartment', function ($q) use ($depId) {
                $q->where('department_id_fk', $depId);
            })
            ->whereDoesntHave('roles', function ($q) use ($depId) {
                $q->where('role_dep_id_fk', $depId)
                  ->whereIn('gen_role', ['Head', 'Procurement', 'Supply']);
            })
            ->where('user_id', '!=', $user->user_id)
            ->get();
        }

        // Redirect user based on role
        return match ($userRole) {
            'Head'             => view('head/pages/head-tasks', compact('tasks', 'departmentBudget', 'activeAppId', 'activeAppItems', 'usedAppItemIds', 'userRole', 'subordinates')),
            null, 'Unassigned' => view('unassigned/pages/unassigned-tasks', compact('tasks', 'departmentBudget', 'activeAppId', 'activeAppItems', 'usedAppItemIds', 'userRole', 'subordinates')),
            'Procurement'      => view('procurement/pages/procurement-tasks', compact('tasks', 'departmentBudget', 'activeAppId', 'activeAppItems', 'usedAppItemIds', 'userRole', 'subordinates')),
            'Supply'           => view('supply/pages/supply-tasks', compact('tasks', 'departmentBudget', 'activeAppId', 'activeAppItems', 'usedAppItemIds', 'userRole', 'subordinates')),
            default            => abort(403),
        };
    }

    public function createFromAppItems(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'items' => 'required|array',
            'items.*' => 'exists:app_items_tbl,app_item_id'
        ]);

        // Resolve active department from session role
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $depId = $activeRole ? $activeRole->role_dep_id_fk : null;

        // Get the selected items
        $itemIds = $request->input('items');
        $items = \App\Models\AppItem::whereIn('app_item_id', $itemIds)->get();

        // Build a description/purpose from the project titles
        $projectTitles = $items->pluck('app_item_proj_title')->filter()->unique()->implode(', ');
        $description = "Direct Purchase Request Creation for: " . ($projectTitles ?: 'Selected APP Projects');

        // Create the new task
        $task = \App\Models\Task::create([
            'assigned_by'      => $user->user_id,
            'assigned_to'      => $user->user_id,
            'task_description' => substr($description, 0, 255),
            'created_at'       => now(),
            'pr_id_fk'         => null,
            'task_type'        => 'PR Assignment',
            'is_deleted'       => 0,
            'task_status'      => 'Pending',
            'task_dep_id_fk'   => $depId,
        ]);

        // Associate the selected APP items with this task
        $task->appItems()->attach($itemIds);

        return response()->json([
            'success'      => true,
            'task_id'      => $task->task_id,
            'redirect_url' => route('show.create.pr', ['task_id' => $task->task_id])
        ]);
    }
}
