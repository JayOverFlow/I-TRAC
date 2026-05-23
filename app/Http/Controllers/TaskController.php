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

        // Build the query to fetch tasks assigned to the logged-in user
        $tasksQuery = $user->tasks()
            ->with('assignedBy')
            ->where('is_deleted', 0);

        // For Head users, dynamically filter and scope task records strictly to the active department context
        if ($userRole === 'Head' && $depId) {
            $tasksQuery->where(function ($query) use ($depId) {
                // Task has a purchaseRequest submitted in the active department
                $query->whereHas('purchaseRequest', function ($q) use ($depId) {
                    $q->where('pr_department', $depId);
                })
                // OR task is linked to App items belonging to the active department
                ->orWhereHas('appItems.app', function ($q) use ($depId) {
                    $q->where('app_dep_id_fk', $depId);
                });
            });
        }

        $tasks = $tasksQuery->orderByDesc('created_at')->get();

        // Redirect user based on role
        return match ($userRole) {
            'Head'             => view('head/pages/head-tasks', compact('tasks')), // Fix this
            null, 'Unassigned' => view('unassigned/pages/unassigned-tasks', compact('tasks')), // Unassigned (No role) users
            'Procurement'      => view('procurement/pages/procurement-tasks', compact('tasks')),
            'Supply'           => view('supply/pages/supply-tasks', compact('tasks')),
            default            => abort(403),
        };
    }
}
