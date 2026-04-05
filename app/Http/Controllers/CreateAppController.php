<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\AppParent;
use App\Models\AppItem;

class CreateAppController extends Controller
{
    public function showCreateApp() {
        return view('head/pages/head-create-app');
    }

    public function createApp(Request $request)
    {
        // Simple validation — all fields nullable, esti_budget must be numeric if present
        $validator = Validator::make($request->all(), [
            'items' => 'nullable|array',
            'items.*.proj_title' => 'nullable|string|max:100',
            'items.*.end_user' => 'nullable|string|max:100',
            'items.*.gen_desc' => 'nullable|string|max:50',
            'items.*.mode' => 'nullable|string|max:100',
            'items.*.criteria' => 'nullable|string|max:45',
            'items.*.covered' => 'nullable|string|max:10',
            'items.*.start' => 'nullable|date',
            'items.*.end' => 'nullable|string|max:45',
            'items.*.source' => 'nullable|string|max:45',
            'items.*.esti_budget' => 'nullable|numeric',
            'items.*.tools' => 'nullable|string|max:45',
            'items.*.remarks' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get the authenticated user's department
        $user = Auth::user();
        $department = $user->departments()->first();
        $depId = $department ? $department->dep_id : null;

        // Create the parent APP record
        $app = AppParent::create([
            'saved_by_user_id_fk' => $user->user_id,
            'app_dep_id_fk' => $depId,
            // Leave these columns null for now
            'app_prepared_by_name' => null,
            'app_prepared_by_designation' => null,
            'app_recommending_by_name' => null,
            'app_recommending_by_designation' => null,
            'app_approved_by_name' => null,
            'app_approved_by_designation' => null,
        ]);

        // Create child APP items
        $items = $request->input('items', []);
        foreach ($items as $item) {
            AppItem::create([
                'app_id_fk' => $app->app_id,
                'app_item_proj_title' => $item['proj_title'] ?? null,
                'app_items_end_user' => $item['end_user'] ?? null,
                'app_items_gen_desc' => $item['gen_desc'] ?? null,
                'app_items_mode' => $item['mode'] ?? null,
                'app_items_criteria' => $item['criteria'] ?? null,
                'app_items_covered' => $item['covered'] ?? null,
                'app_items_start' => $item['start'] ?? null,
                'app_items_end' => $item['end'] ?? null,
                'app_items_source' => $item['source'] ?? null,
                'app_items_esti_budget' => $item['esti_budget'] ?? null,
                'app_items_tools' => $item['tools'] ?? null,
                'app_items_remarks' => $item['remarks'] ?? null,
            ]);
        }

        return redirect()->route('show.create-app')->with('success', 'Annual Procurement Plan created successfully!');
    }
}
