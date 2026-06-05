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
        $fiscalYear = '—';

        if (in_array($userRole, ['Head', 'Procurement', 'Supply'])) {
            $depName = $activeRole && $activeRole->department ? $activeRole->department->dep_name : ($user->departments()->first()?->dep_name ?? 'N/A');
            $depId = $activeRole ? $activeRole->role_dep_id_fk : ($user->departments()->first()?->dep_id);

            if ($depId) {
                $activeAppId = session('active_app_id_' . $depId);
                
                // Fallback to the latest Done APP of this department if none set in session
                if (!$activeAppId) {
                    $latestDoneApp = \App\Models\AppParent::where('app_dep_id_fk', $depId)
                        ->where('app_status', 'Done')
                        ->orderByDesc('created_at')
                        ->first();
                    if ($latestDoneApp) {
                        $activeAppId = $latestDoneApp->app_id;
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
                }
            }
        }

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/head-dashboard', compact('depName', 'departmentBudget', 'fiscalYear')),
            'Procurement' => view('procurement/pages/procurement-dashboard', compact('depName', 'departmentBudget', 'fiscalYear')),
            'Supply'      => view('supply/pages/supply-dashboard', compact('depName', 'departmentBudget', 'fiscalYear')),
            default       => view('errors.403'),
        };
    }
}
