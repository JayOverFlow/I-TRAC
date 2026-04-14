<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
class CreatePrController extends Controller
{
    // public function showCreatePr($task_id)
    // {
    //     $task = Task::with('appItems')->findOrFail($task_id);

    //     // Optional: Ensure only the assigned user can view their PR task
    //     if ($task->assigned_to !== auth()->user()->user_id) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     return view('general-pages/create-pr', compact('task'));
    // }

    public function showCreatePr($task_id) {
        // Get the authenticated user
        $user = Auth::user();

        // Get the user role
        $userRole = $user->roles->first()?->gen_role;

        $task = Task::with('appItems')->findOrFail($task_id);

        // Optional: Ensure only the assigned user can view their PR task
        if ($task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/head-create-pr', compact('task')),
            null          => view('unassigned/pages/unassigned-create-pr', compact('task')), // Unassinged (No role) users
            // 'Supply'      => view('supply.dashboard'),
            default       => view('errors.403'),
        };
    }
}
