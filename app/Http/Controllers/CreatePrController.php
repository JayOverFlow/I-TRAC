<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class CreatePrController extends Controller
{

    public function showCreatePr($task_id)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Get the user role
        $userRole = $user->roles->first()?->gen_role;

        $task = Task::with('appItems')->findOrFail($task_id);

        // Optional: Ensure only the assigned user can view their PR task
        if ($task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Group items by project title
        $groupedItems = $task->appItems->groupBy('app_item_proj_title');

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/head-create-pr', compact('task', 'groupedItems')),
            null          => view('unassigned/pages/unassigned-create-pr', compact('task', 'groupedItems')), // Unassinged (No role) users
            // 'Supply'      => view('supply.dashboard'),
            default       => view('errors.403'),
        };
    }
}
