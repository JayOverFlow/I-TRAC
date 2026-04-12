<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
class CreatePrController extends Controller
{
    public function showCreatePr($task_id)
    {
        $task = Task::with('appItems')->findOrFail($task_id);

        // Optional: Ensure only the assigned user can view their PR task
        if ($task->assigned_to !== auth()->user()->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('general-pages/create-pr', compact('task'));
    }
}
