<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\PrParent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrReviewController extends Controller
{
    /**
     * Display the submitted PR for Head to review.
     */
    public function showPrReview($task_id)
    {
        $user = Auth::user();

        $task = Task::findOrFail($task_id);

        // Ensure this is a PR Review task assigned to the current user
        if ($task->task_type !== 'PR Review' || $task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Load the PR with items (eager load specs and appItem for project title)
        $pr = PrParent::with(['prItems.prSpecs', 'prItems.appItem', 'department', 'requestor'])
            ->findOrFail($task->pr_id_fk);

        // Category display order
        $categoryOrder = [
            'consumable'    => 'Consumables',
            'equipment'     => 'Equipment',
            'equipment_50k' => 'Equipment (50k & ↑)',
        ];

        // Group items by project title, then by category within each project
        $groupedItems = $pr->prItems
            ->groupBy(fn($item) => $item->appItem->app_item_proj_title ?? 'Untitled Project')
            ->map(function ($items) use ($categoryOrder) {
                // Group by category within this project, ordered by $categoryOrder
                $byCategory = $items->groupBy('pr_items_category');
                $sorted = collect();
                foreach ($categoryOrder as $key => $label) {
                    if ($byCategory->has($key)) {
                        $sorted->put($key, $byCategory->get($key));
                    }
                }
                return $sorted;
            });

        return view('head.pages.head-pr-review', compact('task', 'pr', 'groupedItems', 'categoryOrder'));
    }

    /**
     * Approve the PR — update PR status and both task statuses.
     */
    public function approvePr($task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        if ($task->task_type !== 'PR Review' || $task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($task) {
            // Update the PR status
            $pr = PrParent::findOrFail($task->pr_id_fk);
            $pr->update([
                'pr_status' => 'Approved',
                'approved_at' => now(),
            ]);

            // Update the Head's PR Review task
            $task->update(['task_status' => 'Approved']);

            // Update the subordinate's original Purchase Request task
            Task::where('pr_id_fk', $pr->pr_id)
                ->where('task_type', 'Purchase Request')
                ->update(['task_status' => 'Approved']);
        });

        return redirect()->route('show.tasks')
            ->with('success', 'Purchase Request has been approved.');
    }

    /**
     * Cancel the PR Approval — revert PR status and both task statuses back to Pending.
     */
    public function cancelApprovePr($task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        if ($task->task_type !== 'PR Review' || $task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($task) {
            $pr = PrParent::findOrFail($task->pr_id_fk);

            // Validate if exactly within 7 days
            if ($pr->approved_at && \Carbon\Carbon::parse($pr->approved_at)->diffInDays(now()) >= 7) {
                // If it's already past 7 days, we cannot cancel
                abort(403, 'Cancellation period has expired.');
            }

            // Revert PR status to Pending and remove approved_at
            $pr->update([
                'pr_status' => 'Pending',
                'approved_at' => null,
            ]);

            // Revert the Head's PR Review task
            $task->update(['task_status' => 'Pending']);

            // Revert the subordinate's original Purchase Request task
            Task::where('pr_id_fk', $pr->pr_id)
                ->where('task_type', 'Purchase Request')
                ->update(['task_status' => 'Pending']);
        });

        return redirect()->back()
            ->with('success', 'Purchase Request approval has been cancelled.');
    }

    /**
     * Reject the PR — update PR status and both task statuses.
     */
    public function rejectPr($task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        if ($task->task_type !== 'PR Review' || $task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($task) {
            // Update the PR status
            $pr = PrParent::findOrFail($task->pr_id_fk);
            $pr->update(['pr_status' => 'Rejected']);

            // Update the Head's PR Review task
            $task->update(['task_status' => 'Rejected']);

            // Update the subordinate's original Purchase Request task
            Task::where('pr_id_fk', $pr->pr_id)
                ->where('task_type', 'Purchase Request')
                ->update(['task_status' => 'Rejected']);
        });

        return redirect()->route('show.tasks')
            ->with('success', 'Purchase Request has been rejected.');
    }
}
