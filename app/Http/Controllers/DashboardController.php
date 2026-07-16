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
        $activeApp = null;
        $departmentBudget = 0;
        $utilizedBudget = 0;
        $fiscalYear = '—';
        $subordinates = collect();
        $activeAppId = null;
        $recentProcuredItems = collect();
        $availableYears = collect();
        $selectedYear = request('year');

        if (in_array($userRole, ['Head', 'Procurement', 'Supply'])) {
            $depName = $activeRole && $activeRole->department ? $activeRole->department->dep_name : ($user->departments()->first()?->dep_name ?? 'N/A');
            $depId = $activeRole ? $activeRole->role_dep_id_fk : ($user->departments()->first()?->dep_id);

            if ($depId) {
                // Query all available APP fiscal years for this department
                $availableYears = \App\Models\AppParent::where('app_dep_id_fk', $depId)
                    ->whereNotNull('app_year')
                    ->pluck('app_year')
                    ->unique()
                    ->sort()
                    ->values();

                if ($availableYears->isEmpty()) {
                    $availableYears = collect([date('Y')]);
                }

                // If year request parameter is set, find APP of that year. Otherwise, look for session/active APP.
                if ($selectedYear) {
                    $targetApp = \App\Models\AppParent::where('app_dep_id_fk', $depId)
                        ->where('app_year', $selectedYear)
                        ->orderBy('is_active', 'desc')
                        ->first();
                    $activeAppId = $targetApp?->app_id;
                } else {
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
                }

                // Wrap multiple queries to calculate budget in database transaction
                $budgetData = \Illuminate\Support\Facades\DB::transaction(function () use ($activeAppId) {
                    $activeApp = $activeAppId ? \App\Models\AppParent::with('appItems')->find($activeAppId) : null;
                    $utilizedBudget = 0.0;
                    if ($activeApp) {
                        $utilizedBudget = (float) ($activeApp->utilized_budget ?? 0.0);
                    }
                    return compact('activeApp', 'utilizedBudget');
                });

                $activeApp = $budgetData['activeApp'];
                $utilizedBudget = $budgetData['utilizedBudget'];

                if ($activeApp) {
                    $fiscalYear = $activeApp->app_year ?? '2026';
                    $departmentBudget = $activeApp->app_total ?? $activeApp->appItems->sum('app_items_esti_budget');
                }

                if (!$selectedYear) {
                    $selectedYear = $fiscalYear !== '—' ? $fiscalYear : date('Y');
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

                // Query top 5 most recently procured items for the office
                $recentProcuredItems = \App\Models\Mr::with(['poItem.purchaseOrder'])
                    ->whereHas('poItem.purchaseOrder.purchaseRequest', function ($query) use ($depId, $activeAppId) {
                        $query->where('pr_department', $depId);
                        if ($activeAppId) {
                            $query->where('app_id_fk', $activeAppId);
                        }
                    })
                    ->orderBy('mr_id', 'desc')
                    ->take(5)
                    ->get();

                /* DUMMY DATA FOR TESTING - TO USE REAL DATA, JUST COMMENT OUT THIS BLOCK */
                $recentProcuredItems = collect([
                    (object)[
                        'item_name' => 'Premium Ergonomic Mesh Chair',
                        'quantity' => 2,
                        'poItem' => (object)[
                            'po_items_cost' => 6499.00,
                            'purchaseOrder' => (object)['po_no' => 'PO-2026-0038']
                        ]
                    ],
                    (object)[
                        'item_name' => 'LED Multimedia Projector 4K',
                        'quantity' => 1,
                        'poItem' => (object)[
                            'po_items_cost' => 24500.00,
                            'purchaseOrder' => (object)['po_no' => 'PO-2026-0042']
                        ]
                    ],
                    (object)[
                        'item_name' => 'High-Speed Wireless Router',
                        'quantity' => 3,
                        'poItem' => (object)[
                            'po_items_cost' => 4200.00,
                            'purchaseOrder' => (object)['po_no' => 'PO-2026-0035']
                        ]
                    ],
                    (object)[
                        'item_name' => 'Office Whiteboard (4x3 ft)',
                        'quantity' => 1,
                        'poItem' => (object)[
                            'po_items_cost' => 3200.00,
                            'purchaseOrder' => (object)['po_no' => 'PO-2026-0029']
                        ]
                    ],
                    (object)[
                        'item_name' => 'Heavy-Duty Paper Shredder',
                        'quantity' => 1,
                        'poItem' => (object)[
                            'po_items_cost' => 8950.00,
                            'purchaseOrder' => (object)['po_no' => 'PO-2026-0021']
                        ]
                    ]
                ]);
                /* END OF DUMMY DATA */
            }
        }

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/head-dashboard', compact('depName', 'departmentBudget', 'fiscalYear', 'subordinates', 'utilizedBudget', 'activeAppId', 'recentProcuredItems', 'availableYears', 'selectedYear', 'activeApp')),
            'Procurement' => view('procurement/pages/procurement-dashboard', compact('depName', 'departmentBudget', 'fiscalYear', 'subordinates', 'utilizedBudget', 'activeAppId', 'recentProcuredItems', 'availableYears', 'selectedYear', 'activeApp')),
            'Supply'      => view('supply/pages/supply-dashboard', compact('depName', 'departmentBudget', 'fiscalYear', 'subordinates', 'utilizedBudget', 'activeAppId', 'recentProcuredItems', 'availableYears', 'selectedYear', 'activeApp')),
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
