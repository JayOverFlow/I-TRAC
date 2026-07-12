<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\AppParent;
use App\Models\AppItem;

class CreateAppController extends Controller
{
    public function showCreateApp($app_id = null)
    {
        if (!$app_id) {
            return redirect()->route('account.settings')->with('error', 'Please create a new procurement plan from the Annual Procurement Plan tab.');
        }

        $app_data = AppParent::with('appItems')->findOrFail($app_id);

        // Scope the APP strictly to the active department context to prevent cross-department tampering.
        $user = auth()->user();
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $dep_id = $activeRole ? $activeRole->role_dep_id_fk : null;

        if ($app_data->app_dep_id_fk !== $dep_id) {
            abort(403, 'Unauthorized access to this APP record.');
        }

        $isReadOnly = ($app_data->app_status === 'Done');

        $breadcrumbs = [
            ['title' => 'Account Settings', 'url' => route('account.settings')],
            ['title' => $isReadOnly ? 'View APP' : 'Create APP', 'url' => '']
        ];

        return view('head/pages/head-create-app', compact('app_data', 'breadcrumbs', 'isReadOnly'));
    }

    public function initApp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_title' => 'required|string|min:5|max:150',
            'year'      => 'required|integer|min:2020|max:2099',
        ], [
            'app_title.required' => 'Procurement Plan Title is required.',
            'app_title.min'      => 'Title must be at least 5 characters.',
            'app_title.max'      => 'Title must not exceed 150 characters.',
            'year.required'      => 'Year is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $depId = $activeRole ? $activeRole->role_dep_id_fk : ($user->departments()->first()?->dep_id);

        $year = $request->input('year');
        $appCount = AppParent::where('app_dep_id_fk', $depId)
            ->where('app_unique_code', 'like', 'APP-' . $year . '-%')
            ->count() + 1;
        $appUniqueCode = 'APP-' . $year . '-' . str_pad($appCount, 2, '0', STR_PAD_LEFT);

        $app = AppParent::create([
            'app_title'           => $request->input('app_title') . " " . $year,
            'saved_by_user_id_fk' => $user->user_id,
            'app_dep_id_fk'       => $depId,
            'app_unique_code'     => $appUniqueCode,
            'app_status'          => 'Draft',
            'app_total'           => 0,
        ]);

        session()->flash('success', 'Annual Procurement Plan initialized successfully.');

        return response()->json([
            'success'  => true,
            'redirect' => route('show.create-app', ['app_id' => $app->app_id])
        ]);
    }


    public function createApp(Request $request)
    {
        $intent = $request->input('_intent', 'done'); // 'done' or 'draft'

        // --- Build validation rules based on intent ---
        if ($intent === 'done') {
            // Strict: enforce all required fields from app_form.json
            $rules = [
                'items'                  => 'required|array|min:1',
                'items.*.proj_title'     => 'required|string|min:5|max:100',
                'items.*.end_user'       => 'nullable|string|min:5|max:100',
                'items.*.gen_desc'       => 'nullable|string|min:5|max:50',
                'items.*.mode'           => 'nullable|string|min:5|max:100',
                'items.*.criteria'       => 'nullable|string|min:2|max:45',
                'items.*.covered'        => 'required|in:Yes,No',
                'items.*.start'          => 'required|date',
                'items.*.end'            => 'required|date',
                'items.*.source'         => 'nullable|string|min:2|max:45',
                'items.*.esti_budget'    => 'nullable|numeric|min:1|max:9999999999',
                'items.*.tools'          => 'nullable|string|min:5|max:45',
                'items.*.remarks'        => 'nullable|string|min:5|max:50',
            ];
        } else {
            // Lenient: only proj_title required per item
            $rules = [
                'items'                  => 'required|array|min:1',
                'items.*.proj_title'     => 'required|string|min:5|max:100',
                'items.*.end_user'       => 'nullable|string|min:5|max:100',
                'items.*.gen_desc'       => 'nullable|string|min:5|max:50',
                'items.*.mode'           => 'nullable|string|min:5|max:100',
                'items.*.criteria'       => 'nullable|string|min:2|max:45',
                'items.*.covered'        => 'nullable|in:Yes,No',
                'items.*.start'          => 'nullable|date',
                'items.*.end'            => 'nullable|date',
                'items.*.source'         => 'nullable|string|min:2|max:45',
                'items.*.esti_budget'    => 'nullable|numeric|min:1|max:9999999999',
                'items.*.tools'          => 'nullable|string|min:5|max:45',
                'items.*.remarks'        => 'nullable|string|min:5|max:50',
            ];
        }

        // Short, descriptive custom messages
        $messages = [
            'items.required'                  => 'At least one project item is required.',
            'items.*.proj_title.required'     => 'Project title is required.',
            'items.*.proj_title.min'          => 'Title must be at least 5 characters.',
            'items.*.proj_title.max'          => 'Title must not exceed 100 characters.',
            'items.*.end_user.min'            => 'End-user must be at least 5 characters.',
            'items.*.end_user.max'            => 'End-user must not exceed 100 characters.',
            'items.*.gen_desc.min'            => 'Description must be at least 5 characters.',
            'items.*.gen_desc.max'            => 'Description must not exceed 50 characters.',
            'items.*.mode.min'                => 'Mode must be at least 5 characters.',
            'items.*.mode.max'                => 'Mode must not exceed 100 characters.',
            'items.*.criteria.min'            => 'Criteria must be at least 2 characters.',
            'items.*.criteria.max'            => 'Criteria must not exceed 45 characters.',
            'items.*.covered.required'        => 'Please select Yes or No.',
            'items.*.covered.in'              => 'Invalid value for Early Procurement.',
            'items.*.start.required'          => 'Start date is required.',
            'items.*.start.date'              => 'Start date must be a valid date.',
            'items.*.end.required'            => 'End date is required.',
            'items.*.end.date'                => 'End date must be a valid date.',
            'items.*.source.min'              => 'Source must be at least 2 characters.',
            'items.*.source.max'              => 'Source must not exceed 45 characters.',
            'items.*.esti_budget.numeric'     => 'Budget must be a number.',
            'items.*.esti_budget.min'         => 'Budget must be at least 1.',
            'items.*.esti_budget.max'         => 'Budget value is too large.',
            'items.*.tools.min'               => 'Tools must be at least 5 characters.',
            'items.*.tools.max'               => 'Tools must not exceed 45 characters.',
            'items.*.remarks.min'             => 'Remarks must be at least 5 characters.',
            'items.*.remarks.max'             => 'Remarks must not exceed 50 characters.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Dynamically resolve the active role & active department from the active session context.
        // This ensures the APP is created strictly under the department the user is currently working on.
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $depId = $activeRole ? $activeRole->role_dep_id_fk : ($user->departments()->first()?->dep_id);

        $appId = $request->input('app_id');

        $existingItemIds = [];
        if ($appId) {
            $app = AppParent::findOrFail($appId);

            // Scope safety check
            if ($app->app_dep_id_fk !== $depId) {
                abort(403, 'Unauthorized access.');
            }

            // Allow modifying completed APPs to make form editing more flexible.


            // Update status and ensure unique code exists
            $appData = [
                'app_status' => $intent === 'done' ? 'Done' : 'Draft',
            ];

            // Set app_title if not already present
            if (!$app->app_title) {
                $appVersion = AppParent::where('app_dep_id_fk', $app->app_dep_id_fk)
                    ->where('app_id', '<=', $app->app_id)
                    ->count();
                $appData['app_title'] = $appVersion <= 1
                    ? "Annual Procurement Plan for Fiscal Year 2026"
                    : "Annual Procurement Plan for Fiscal Year 2026 Version " . $appVersion;
            }

            if (!$app->app_unique_code) {
                $year = date('Y');
                $appCount = AppParent::where('app_dep_id_fk', $app->app_dep_id_fk)
                    ->whereYear('created_at', $year)
                    ->count() + 1;
                $appData['app_unique_code'] = 'APP-' . $year . '-' . str_pad($appCount, 2, '0', STR_PAD_LEFT);
            }
            $app->update($appData);

            $existingItemIds = AppItem::where('app_id_fk', $app->app_id)->pluck('app_item_id')->toArray();
        } else {
            $year = date('Y');
            $appCount = AppParent::where('app_dep_id_fk', $depId)
                ->whereYear('created_at', $year)
                ->count() + 1;
            $appUniqueCode = 'APP-' . $year . '-' . str_pad($appCount, 2, '0', STR_PAD_LEFT);

            $appTitle = $appCount === 1
                ? "Annual Procurement Plan for Fiscal Year 2026"
                : "Annual Procurement Plan for Fiscal Year 2026 Version " . $appCount;

            $app = AppParent::create([
                'app_title'           => $appTitle,
                'saved_by_user_id_fk' => $user->user_id,
                'app_dep_id_fk'       => $depId,
                'app_unique_code'     => $appUniqueCode,
                'app_status'          => $intent === 'done' ? 'Done' : 'Draft',
            ]);
        }


        $submittedItemIds = [];
        $totalEstiBudget = 0;
        foreach ($request->input('items', []) as $item) {
            $estiBudget = isset($item['esti_budget']) ? (float)$item['esti_budget'] : 0;
            $totalEstiBudget += $estiBudget;

            $itemId = $item['app_item_id'] ?? null;

            if ($itemId && in_array($itemId, $existingItemIds)) {
                $submittedItemIds[] = $itemId;
                $existingItem = AppItem::find($itemId);

                // If the item is already assigned, preserve its values to prevent tampering
                if ($existingItem->app_items_assigned_to !== null) {
                    continue;
                }

                $existingItem->update([
                    'app_item_proj_title'    => $item['proj_title']   ?? $existingItem->app_item_proj_title,
                    'app_items_end_user'     => $item['end_user']     ?? $existingItem->app_items_end_user,
                    'app_items_gen_desc'     => $item['gen_desc']     ?? $existingItem->app_items_gen_desc,
                    'app_items_mode'         => $item['mode']         ?? $existingItem->app_items_mode,
                    'app_items_criteria'     => $item['criteria']     ?? $existingItem->app_items_criteria,
                    'app_items_covered'      => $item['covered']      ?? $existingItem->app_items_covered,
                    'app_items_start'        => $item['start']        ?? $existingItem->app_items_start,
                    'app_items_end'          => $item['end']          ?? $existingItem->app_items_end,
                    'app_items_source'       => $item['source']       ?? $existingItem->app_items_source,
                    'app_items_esti_budget'  => $item['esti_budget']  ?? $existingItem->app_items_esti_budget,
                    'app_items_tools'        => $item['tools']        ?? $existingItem->app_items_tools,
                    'app_items_remarks'      => $item['remarks']      ?? $existingItem->app_items_remarks,
                ]);
            } else {
                AppItem::create([
                    'app_id_fk'              => $app->app_id,
                    'app_item_proj_title'    => $item['proj_title']   ?? null,
                    'app_items_end_user'     => $item['end_user']     ?? null,
                    'app_items_gen_desc'     => $item['gen_desc']     ?? null,
                    'app_items_mode'         => $item['mode']         ?? null,
                    'app_items_criteria'     => $item['criteria']     ?? null,
                    'app_items_covered'      => $item['covered']      ?? null,
                    'app_items_start'        => $item['start']        ?? null,
                    'app_items_end'          => $item['end']          ?? null,
                    'app_items_source'       => $item['source']       ?? null,
                    'app_items_esti_budget'  => $item['esti_budget']  ?? null,
                    'app_items_tools'        => $item['tools']        ?? null,
                    'app_items_remarks'      => $item['remarks']      ?? null,
                ]);
            }
        }

        // Delete any existing items that were not submitted (removed on frontend)
        if ($appId) {
            $toDeleteIds = array_diff($existingItemIds, $submittedItemIds);
            if (!empty($toDeleteIds)) {
                // Security check: Make sure none of the items being deleted are assigned!
                $assignedToDelete = AppItem::whereIn('app_item_id', $toDeleteIds)
                    ->whereNotNull('app_items_assigned_to')
                    ->count();
                if ($assignedToDelete > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete items that are already assigned to a Purchase Request task.',
                    ], 422);
                }
                AppItem::whereIn('app_item_id', $toDeleteIds)->delete();
            }
        }

        // Set allocated budget (app_total) dynamically: set to Estimated Budget sum if Done, 0 if Draft
        $allocatedBudget = ($intent === 'done') ? $totalEstiBudget : 0;
        $app->update([
            'app_total' => $allocatedBudget,
        ]);

        $message = $intent === 'done'
            ? 'Annual Procurement Plan submitted successfully!'
            : 'Draft saved successfully!';

        session()->flash('success', $message);

        $redirectUrl = route('account.settings') . '#pane-animated-underline-annual-procurement-plan';

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => $redirectUrl,
        ]);
    }
}
