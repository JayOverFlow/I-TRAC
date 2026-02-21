<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function showTasks() {
        // Get the currently logged in user
        // $user = Auth::user();

        return view('head/pages/head-tasks');

    }
}
