<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('dep_name', 'asc')
            ->get(['dep_id', 'dep_name']);

        return response()->json($departments);
    }
}
