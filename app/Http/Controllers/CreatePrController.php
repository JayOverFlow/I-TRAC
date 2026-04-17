<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\PrParent;
use App\Models\PrItem;
use App\Models\PrSpec;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePrController extends Controller
{

    public function showCreatePr($task_id)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Get the user role
        $userRole = $user->roles->first()?->gen_role;

        $task = Task::with('appItems')->findOrFail($task_id);

        // Optional: Ensure only the assigned user can view their PR task
        if ($task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Group items by project title
        $groupedItems = $task->appItems->groupBy('app_item_proj_title');

        // Redirect user based on role
        return match ($userRole) {
            'Head'        => view('head/pages/head-create-pr', compact('task', 'groupedItems')),
            null          => view('unassigned/pages/unassigned-create-pr', compact('task', 'groupedItems')), // Unassinged (No role) users
            // 'Supply'      => view('supply.dashboard'),
            default       => view('errors.403'),
        };
    }

    public function createPr(Request $request, $task_id)
    {

        $user = Auth::user();

        // Get the user's department ID from their role
        $departmentId = $user->roles->first()?->role_dep_id_fk;

        try {
            // Wrap all inserts in a transaction — if anything fails, nothing is saved
            DB::transaction(function () use ($request, $user, $departmentId, $task_id) {

                // Step 1: Create the PR header row in pr_tbl
                $pr = PrParent::create([
                    'pr_section'           => $request->input('pr_section'),
                    'pr_department'        => $departmentId,
                    'pr_no'                => $request->input('pr_no'),
                    'pr_date'              => now()->toDateString(),
                    'pr_name_of_requestor' => $user->user_id,
                    'saved_by_user_id_fk'  => $user->user_id,
                    'pr_unique_code'       => strtoupper(Str::random(8)),
                ]);

                // Step 2: Loop through every item row submitted by the form.
                // The array key ($appItemId) IS the app_item_id — encoded directly in the form field name.
                foreach ($request->input('items', []) as $appItemId => $row) {

                    // Skip rows that are effectively blank (no description and no quantity)
                    if (empty($row['description']) && empty($row['quantity'])) {
                        continue;
                    }

                    // Map the form's Category dropdown text to the DB enum value
                    $categoryMap = [
                        'Consumable'           => 'consumable',
                        'Equipment'            => 'equipment',
                        'Equipment (50k & ↑)'  => 'equipment_50k',
                    ];
                    $category = $categoryMap[$row['category'] ?? ''] ?? null;

                    $qty  = (int)   ($row['quantity'] ?? 0);
                    $cost = (float) ($row['cost']     ?? 0);

                    // Step 3: Save the item row in pr_items_tbl
                    $prItem = PrItem::create([
                        'pr_id_fk'            => $pr->pr_id,
                        'pr_app_item_id_fk'   => $appItemId,  // comes from the array key, never null
                        'pr_items_descrip'    => $row['description']  ?? null,
                        'pr_items_unit'       => $row['unit']         ?? null,
                        'pr_items_quantity'   => $qty,
                        'pr_items_cost'       => $cost,
                        'pr_items_total_cost' => $qty * $cost,
                        'pr_items_category'   => $category,
                    ]);

                    // Step 4: If the user filled in a specification, save it in pr_items_specs_tbl
                    if (!empty($row['specification'])) {
                        PrSpec::create([
                            'pr_items_id_fk' => $prItem->pr_items_id,
                            'pr_spec_spec'   => $row['specification'],
                        ]);
                    }
                }

                // Step 5: Update task status to indicate PR has been created
                Task::where('task_id', $task_id)->update(['task_status' => 'completed']);
            });
            // Redirect to tasks page with a success flash message
            return redirect()->route('show.tasks')->with('success', 'Purchase Request submitted successfully.');
        } catch (\Exception $e) {
            // Log the error if necessary
            // \Log::error($e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Something went wrong while saving the PR. Please try again.');
        }
    }
}
