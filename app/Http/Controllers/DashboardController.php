<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function showDashboard() {
        if (request()->has('error_no_active_app')) {
            return redirect()->route('show.dashboard')->with('error', 'There is no active Annual Procurement Plan for your office.');
        }

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
        $activeAppId = null;

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

                // Wrap multiple queries to calculate budget in database transaction
                $budgetData = \Illuminate\Support\Facades\DB::transaction(function () use ($activeAppId) {
                    $activeApp = $activeAppId ? \App\Models\AppParent::with('appItems')->find($activeAppId) : null;
                    $utilizedBudget = 0.0;
                    if ($activeApp) {
                        $utilizedBudget = (float) \App\Models\IarItem::whereHas('iar.purchaseOrder.purchaseRequest', function ($query) use ($activeAppId) {
                                $query->where('app_id_fk', $activeAppId);
                            })
                            ->whereNotNull('iar_po_items_id_fk')
                            ->join('po_items_tbl', 'iar_items_tbl.iar_po_items_id_fk', '=', 'po_items_tbl.po_items_id')
                            ->sum(\Illuminate\Support\Facades\DB::raw('iar_items_tbl.iar_quantity * po_items_tbl.po_items_cost'));
                    }
                    return compact('activeApp', 'utilizedBudget');
                });

                $activeApp = $budgetData['activeApp'];
                $utilizedBudget = $budgetData['utilizedBudget'];

                if ($activeApp) {
                    if (preg_match('/Fiscal Year (\d{4})/i', $activeApp->app_title, $matches)) {
                        $fiscalYear = $matches[1];
                    } else {
                        $fiscalYear = $activeApp->created_at ? \Carbon\Carbon::parse($activeApp->created_at)->format('Y') : '2026';
                    }
                    $departmentBudget = $activeApp->app_total ?? $activeApp->appItems->sum('app_items_esti_budget');
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
            'Head'        => view('head/pages/head-dashboard', compact('depName', 'departmentBudget', 'fiscalYear', 'subordinates', 'utilizedBudget', 'activeAppId')),
            'Procurement' => view('procurement/pages/procurement-dashboard', compact('depName', 'departmentBudget', 'fiscalYear', 'subordinates', 'utilizedBudget', 'activeAppId')),
            'Supply'      => view('supply/pages/supply-dashboard', compact('depName', 'departmentBudget', 'fiscalYear', 'subordinates', 'utilizedBudget', 'activeAppId')),
            default       => view('errors.403'),
        };
    }

    /**
     * Generate the Utilized Budget Report as a PDF.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportUbr(Request $request)
    {
        $user = Auth::user();
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;

        if (!in_array($userRole, ['Head', 'Procurement', 'Supply'])) {
            abort(403, 'Unauthorized access.');
        }

        $depName = $activeRole && $activeRole->department ? $activeRole->department->dep_name : ($user->departments()->first()?->dep_name ?? 'N/A');
        $depId = $activeRole ? $activeRole->role_dep_id_fk : ($user->departments()->first()?->dep_id);

        if (!$depId) {
            abort(400, 'Department context not found.');
        }

        $activeAppId = session('active_app_id_' . $depId);
        if (!$activeAppId) {
            $dbActiveApp = \App\Models\AppParent::where('app_dep_id_fk', $depId)
                ->where('is_active', true)
                ->first();
            if ($dbActiveApp) {
                $activeAppId = $dbActiveApp->app_id;
            }
        }

        if (!$activeAppId) {
            return redirect()->back()->with('error', 'No active Annual Procurement Plan found for this office.');
        }

        // Query IAR items linked to PO items for the set active APP inside a DB transaction
        $iarItems = \Illuminate\Support\Facades\DB::transaction(function () use ($activeAppId) {
            return \App\Models\IarItem::whereHas('iar.purchaseOrder.purchaseRequest', function ($query) use ($activeAppId) {
                $query->where('app_id_fk', $activeAppId);
            })
            ->whereNotNull('iar_po_items_id_fk')
            ->with(['iar', 'poItem'])
            ->get();
        });

        $asOfDate = now()->format('F Y');

        $pdfService = app(\App\Services\UbrPdfExportService::class);
        return $pdfService->export($depName, $asOfDate, $iarItems);
    }
}
