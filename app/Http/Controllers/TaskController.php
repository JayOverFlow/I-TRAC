<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function showTasks()
    {
        // Get all tasks assigned to the logged-in user, with the sender's user info
        $tasks = Auth::user()
            ->tasks()
            ->with('assignedBy')
            ->where('is_deleted', 0)
            ->orderByDesc('created_at')
            ->get();

        return view('general-pages.tasks', compact('tasks'));
    }
}
