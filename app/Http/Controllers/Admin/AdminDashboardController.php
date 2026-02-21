<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    // Admin dashboard (Users)
    public function index()
    {
        $departments = Department::all();

        // Card counts
        $officesCount = Department::where('dep_type', 'administrative')->count();
        $deptsCount   = Department::where('dep_type', 'academic')->count();
        $facultyCount = User::where('user_type', 'Faculty')->count();
        $staffCount   = User::where('user_type', 'Staff')->count();

        // Table: users with their role and department via joins
        $users = DB::table('users as u')
            ->leftJoin('user_roles_tbl as ur', 'ur.user_id_fk', '=', 'u.user_id')
            ->leftJoin('roles_tbl as r', 'r.role_id', '=', 'ur.role_id_fk')
            ->leftJoin('departments_tbl as d', 'd.dep_id', '=', 'r.role_dep_id_fk')
            ->select(
                'u.user_tupid',
                'u.user_firstname',
                'u.user_lastname',
                'u.user_email',
                'r.role_name',
                'd.dep_name',
                'u.user_type'
            )
            ->get();

        return view('admin.pages.dashboard', compact(
            'departments',
            'officesCount',
            'deptsCount',
            'facultyCount',
            'staffCount',
            'users'
        ));
    }

    // Admin Roles and Offices page
    public function rolesOffices()
    {
        $departments = Department::all();
        return view('admin.pages.roles-offices', compact('departments'));
    }

    // Admin Roles Assignment page
    public function rolesAssignment()
    {
        $departments = Department::all();
        return view('admin.pages.roles-assignment', compact('departments'));
    }
}
