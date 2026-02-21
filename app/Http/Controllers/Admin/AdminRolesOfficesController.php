<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminRolesOfficesController extends Controller
{
    public function index()
    {
        $data = $this->_getCommonData();

        // Fetch Roles and their Departments
        $data['roles'] = DB::table('roles_tbl as r')
            ->join('departments_tbl as d', 'r.role_dep_id_fk', '=', 'd.dep_id')
            ->select('r.role_id', 'r.role_name', 'd.dep_name')
            ->get();

        return view('admin.pages.roles-offices', $data);
    }

    /**
     * Shared logic for card counts and departments list
     */
    private function _getCommonData()
    {
        return [
            'departments'  => Department::all(),
            'officesCount' => Department::where('dep_type', 'administrative')->count(),
            'deptsCount'   => Department::where('dep_type', 'academic')->count(),
            'facultyCount' => User::where('user_type', 'Faculty')->count(),
            'staffCount'   => User::where('user_type', 'Staff')->count(),
        ];
    }
}
