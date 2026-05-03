<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function showTasks() {
        // Get the authenticated user
        $user = Auth::user();

        // Get the user role
        $userRole = $user->roles->first()?->gen_role;

        // Get all tasks assigned to the logged-in user, with the sender's user info
        $tasks = Auth::user()
            ->tasks()
            ->with('assignedBy')
            ->where('is_deleted', 0)
            ->orderByDesc('created_at')
            ->get();

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/head-tasks', compact('tasks')), // Fix this
            null          => view('unassigned/pages/unassigned-tasks', compact('tasks')), // Unassinged (No role) users
            'Procurement' => view('procurement/pages/procurement-tasks', compact('tasks')),
            default       => view('errors.403'),
        };
    }
}
