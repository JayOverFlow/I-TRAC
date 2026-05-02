<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\PrParent;
use App\Models\PrItem;
use App\Models\PrSpec;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreatePrController extends Controller
{

    public function showCreatePr($task_id)
    {
        $user = Auth::user();
        $userRole = $user->roles->first()?->gen_role;

        $task = Task::with('appItems')->findOrFail($task_id);

        // Ensure only the assigned user can view their PR task
        if ($task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Group items by project title
        $groupedItems = $task->appItems->groupBy('app_item_proj_title');

        // Check if a PR already exists for this task
        $pr = null;
        $savedItemsGrouped = collect();

        if ($task->pr_id_fk) {
            $existingPr = PrParent::with(['prItems.prSpecs'])->find($task->pr_id_fk);

            if ($existingPr) {
                $pr = $existingPr;
                // Group saved items by app_item_id to handle multiple rows per item
                $savedItemsGrouped = $existingPr->prItems->groupBy('pr_app_item_id_fk');
            }
        }

        return match ($userRole) {
            'Head'   => view('head/pages/head-create-pr', compact('task', 'groupedItems', 'pr', 'savedItemsGrouped')),
            null     => view('unassigned/pages/unassigned-create-pr', compact('task', 'groupedItems', 'pr', 'savedItemsGrouped')),
            default  => view('errors.403'),
        };
    }

    /**
     * Save or update the PR as a Draft.
     * Task status stays "Pending".
     */
    public function saveDraft(Request $request, $task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        if ($task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($task->task_status, ['Pending', 'Rejected'])) {
            return redirect()->route('show.create.pr', $task_id)
                ->with('error', 'This Purchase Request has already been submitted and can no longer be edited.');
        }

        try {
            DB::transaction(function () use ($request, $user, $task) {
                $pr = $this->saveOrUpdatePr($request, $user, $task, 'Draft');

                // Link PR to task if not already linked
                if (!$task->pr_id_fk) {
                    $task->update(['pr_id_fk' => $pr->pr_id]);
                }
                // Task status stays "Pending" for drafts
            });

            return redirect()->route('show.create.pr', $task_id)
                ->with('success', 'Purchase Request saved as draft.');
        } catch (\Exception $e) {
            Log::error('Draft Save Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Something went wrong while saving the draft. Please try again.');
        }
    }

    /**
     * Submit the PR to the department head.
     * Task status changes to "Submitted".
     */
    public function submitPr(Request $request, $task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        if ($task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($task->task_status, ['Pending', 'Rejected'])) {
            return redirect()->route('show.create.pr', $task_id)
                ->with('error', 'This Purchase Request has already been submitted.');
        }

        try {
            DB::transaction(function () use ($request, $user, $task) {

                // Save/update the PR data first
                $pr = $this->saveOrUpdatePr($request, $user, $task, 'Submitted');

                // Link PR to task if not already linked
                if (!$task->pr_id_fk) {
                    $task->update(['pr_id_fk' => $pr->pr_id]);
                }

                // Mark the original task as Submitted
                $task->update(['task_status' => 'Submitted']);

                // Find the department head of the user's department
                $departmentId = $user->departments->first()?->dep_id;

                if ($departmentId) {
                    // Find the Head role for this department
                    $headRole = Role::where('role_dep_id_fk', $departmentId)
                        ->where('gen_role', 'Head')
                        ->first();

                    if ($headRole && $headRole->user) {
                        $headUserId = $headRole->user->user_id_fk;

                        // Check if a review task already exists for this PR
                        $reviewTask = Task::where('pr_id_fk', $pr->pr_id)
                            ->where('task_type', 'PR Review')
                            ->first();

                        if ($reviewTask) {
                            // Update existing review task back to Pending
                            $reviewTask->update([
                                'task_status'      => 'Pending',
                                'task_description' => 'Revised Purchase Request submitted for review.',
                            ]);
                        } else {
                            // Create a new task for the department head to review
                            Task::create([
                                'assigned_by'      => $user->user_id,
                                'assigned_to'      => $headUserId,
                                'task_description' => 'Purchase Request submitted for review.',
                                'pr_id_fk'         => $pr->pr_id,
                                'task_type'        => 'PR Review',
                                'task_status'      => 'Pending',
                            ]);
                        }
                    }
                }
            });

            return redirect()->route('show.tasks')
                ->with('success', 'Purchase Request submitted successfully.');
        } catch (\Exception $e) {
            Log::error('PR Submit Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Something went wrong while submitting the PR. Please try again.');
        }
    }

    /**
     * Shared helper: create or update a PR record with its items and specs.
     */
    private function saveOrUpdatePr(Request $request, $user, Task $task, string $status): PrParent
    {
        $departmentId = $user->departments->first()?->dep_id;

        // Check if a PR already exists for this task
        $pr = $task->pr_id_fk ? PrParent::find($task->pr_id_fk) : null;

        if ($pr) {
            // Update existing PR header
            $pr->update([
                'pr_section'    => $request->input('pr_section'),
                'pr_no'         => $request->input('pr_no'),
                'pr_department' => $departmentId,
                'pr_purpose'    => $request->input('pr_purpose'),
                'pr_status'     => $status,
                'submitted_at'  => $status === 'Submitted' ? now() : $pr->submitted_at,
            ]);

            // Delete old items (cascades to specs via FK)
            $pr->prItems()->delete();
        } else {
            // Create new PR header
            $pr = PrParent::create([
                'pr_section'           => $request->input('pr_section'),
                'pr_department'        => $departmentId,
                'pr_no'                => $request->input('pr_no'),
                'pr_date'              => now()->toDateString(),
                'pr_purpose'    => $request->input('pr_purpose'),
                'pr_name_of_requestor' => $user->user_id,
                'saved_by_user_id_fk'  => $user->user_id,
                'pr_unique_code'       => strtoupper(Str::random(8)),
                'pr_status'            => $status,
                'submitted_at'         => $status === 'Submitted' ? now() : null,
            ]);
        }

        // Insert items and specs
        foreach ($request->input('items', []) as $row) {

            $appItemId = $row['app_item_id'] ?? null;

            // Skip blank rows or missing app_item_id
            if (!$appItemId || (empty($row['description']) && empty($row['quantity']))) {
                continue;
            }

            $categoryMap = [
                'Consumable'          => 'consumable',
                'Equipment'           => 'equipment',
                'Equipment (50k & ↑)' => 'equipment_50k',
            ];
            $category = $categoryMap[$row['category'] ?? ''] ?? null;

            $qty  = (int)   ($row['quantity'] ?? 0);
            $cost = (float) ($row['cost']     ?? 0);

            $prItem = PrItem::create([
                'pr_id_fk'            => $pr->pr_id,
                'pr_app_item_id_fk'   => $appItemId,
                'pr_items_descrip'    => $row['description']  ?? null,
                'pr_items_unit'       => $row['unit']         ?? null,
                'pr_items_quantity'   => $qty,
                'pr_items_cost'       => $cost,
                'pr_items_category'   => $category,
            ]);

            if (!empty($row['specification'])) {
                PrSpec::create([
                    'pr_items_id_fk' => $prItem->pr_items_id,
                    'pr_spec_spec'   => $row['specification'],
                ]);
            }
        }

        return $pr;
    }
    /**
     * Cancel a submitted PR and return it to Draft status.
     * Allowed only within 3 days of submission.
     */
    public function cancelPr($task_id)
    {
        $user = Auth::user();
        $task = Task::with('purchaseRequest')->findOrFail($task_id);

        if ($task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $pr = $task->purchaseRequest;

        if (!$pr || $task->task_status !== 'Submitted') {
            return redirect()->back()->with('error', 'Only submitted purchase requests can be cancelled.');
        }

        // Check 3-day deadline
        $deadline = \Carbon\Carbon::parse($pr->submitted_at)->addDays(3);
        if (now()->greaterThan($deadline)) {
            return redirect()->back()->with('error', 'Cancellation period (3 days) has expired.');
        }

        try {
            DB::transaction(function () use ($task, $pr) {
                // Revert PR status to Draft
                $pr->update(['pr_status' => 'Draft']);

                // Revert Task status to Pending
                $task->update(['task_status' => 'Pending']);

                // Find and delete the head's review task
                Task::where('pr_id_fk', $pr->pr_id)
                    ->where('task_type', 'PR Review')
                    ->where('task_status', 'Pending') // Only delete if not already being processed
                    ->delete();
            });

            return redirect()->route('show.create.pr', $task_id)
                ->with('success', 'Purchase Request submission cancelled. It is now back to Draft.');
        } catch (\Exception $e) {
            Log::error('PR Cancel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while cancelling the submission.');
        }
    }
}
