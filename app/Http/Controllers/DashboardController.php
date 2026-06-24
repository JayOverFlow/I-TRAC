<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function showDashboard() {
        // Get the authenticated user
        $user = Auth::user();

        // Get the active user role dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;

        // Get necessary data to render
        $depName = 'N/A';
        $departmentBudget = 0;
        $utilizedBudget = 0;
        $fiscalYear = '—';
        $subordinates = collect();

        if (in_array($userRole, ['Head', 'Procurement', 'Supply'])) {
            $depName = $activeRole && $activeRole->department ? $activeRole->department->dep_name : ($user->departments()->first()?->dep_name ?? 'N/A');
            $depId = $activeRole ? $activeRole->role_dep_id_fk : ($user->departments()->first()?->dep_id);

            if ($depId) {
                $activeAppId = session('active_app_id_' . $depId);
                
                // Fetch active APP of this department from database if none set in session
                if (!$activeAppId) {
                    $dbActiveApp = \App\Models\AppParent::where('app_dep_id_fk', $depId)
                        ->where('is_active', true)
                        ->first();
                    if ($dbActiveApp) {
                        $activeAppId = $dbActiveApp->app_id;
                        session(['active_app_id_' . $depId => $activeAppId]);
                    }
                }

                $activeApp = $activeAppId ? \App\Models\AppParent::with('appItems')->find($activeAppId) : null;

                if ($activeApp) {
                    if (preg_match('/Fiscal Year (\d{4})/i', $activeApp->app_title, $matches)) {
                        $fiscalYear = $matches[1];
                    } else {
                        $fiscalYear = $activeApp->created_at ? \Carbon\Carbon::parse($activeApp->created_at)->format('Y') : '2026';
                    }
                    $departmentBudget = $activeApp->app_total ?? $activeApp->appItems->sum('app_items_esti_budget');
                    $utilizedBudget = $activeApp->utilized_budget ?? 0;
                }

                if (in_array($userRole, ['Head', 'Procurement', 'Supply'])) {
                    $subordinates = \DB::table('users as u')
                        ->join('user_departments_tbl as ud', 'u.user_id', '=', 'ud.user_id_fk')
                        ->leftJoin('roles_tbl as r', 'r.role_id', '=', 'ud.role_id_fk')
                        ->where('ud.department_id_fk', $depId)
                        ->where(function($query) {
                            $query->where('r.gen_role', 'Unassigned')
                                  ->orWhereNull('ud.role_id_fk');
                        })
                        ->select(
                            'u.user_id', 'u.user_tupid', 'u.user_firstname',
                            'u.user_lastname', 'u.user_email', 'u.user_type', 'r.role_name',
                            \DB::raw('EXISTS(SELECT 1 FROM tasks_tbl t WHERE t.assigned_to = u.user_id AND (t.is_deleted = 0 OR t.is_deleted IS NULL)) as has_task')
                        )
                        ->get();
                }
            }
        }

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/head-dashboard', compact('depName', 'departmentBudget', 'fiscalYear', 'subordinates', 'utilizedBudget')),
            'Procurement' => view('procurement/pages/procurement-dashboard', compact('depName', 'departmentBudget', 'fiscalYear', 'subordinates', 'utilizedBudget')),
            'Supply'      => view('supply/pages/supply-dashboard', compact('depName', 'departmentBudget', 'fiscalYear', 'subordinates', 'utilizedBudget')),
            default       => view('errors.403'),
        };
    }
}
