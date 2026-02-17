<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function showTasks() {
        // Get the currently logged in user
        $user = Auth::user();

        // Get user tasks
        // TODO: Query the tasks of the user from the database

        // Identify general role
        // TODO: Query the general role of the user from the database
        $genRole = 'Head';

        // Use switch/match case to redirect user
        switch ($genRole) {
        case $genRole === "Head":
            return view('head/pages/head-tasks', compact('user'));
            break;
        case $genRole === "Procurement":
            // code block to execute if expression == value2
            break;
        case $genRole === "Supply":
            // code block to execute if expression == value2
            break;
        case $genRole === "Faculty":
            // code block to execute if expression == value2
            break;
        default:
            // 404 page
            }
        
    }
}
