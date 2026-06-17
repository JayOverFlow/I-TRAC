<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\PrParent;
use App\Models\PrItem;
use App\Models\PrSpec;
use App\Models\AppParent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

class CreatePrController extends Controller
{

    private function authorizeTask(Task $task)
    {
        $user = Auth::user();

        // Resolve active role dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;
        $depId = $activeRole ? $activeRole->role_dep_id_fk : null;

        $isHeadRole = in_array($userRole, ['Head', 'Procurement', 'Supply']);

        // Check authorization:
        // 1. User is assigned to or created the task
        if ($task->assigned_to === $user->user_id || $task->assigned_by === $user->user_id) {
            return;
        }

        // 2. OR user is a Head-level role in the task's department
        if ($isHeadRole && $depId && $task->task_dep_id_fk == $depId) {
            return;
        }

        abort(403, 'Unauthorized action.');
    }

    public function showCreatePr($task_id)
    {
        $user = Auth::user();

        // Resolve active role dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;

        $task = Task::with(['appItems.app', 'purchaseRequest'])->findOrFail($task_id);

        $this->authorizeTask($task);

        // Group items by project title
        $groupedItems = $task->appItems->groupBy('app_item_proj_title');

        // Check if a PR already exists for this task
        $pr = null;
        $savedItemsGrouped = collect();

        if ($task->pr_id_fk) {
            $existingPr = PrParent::with([
                'prItems.prSpecs',
                'purchaseOrders.iarReports',
                'purchaseOrders.risSlips',
                'purchaseOrders.icsSlips',
                'purchaseOrders.parReceipts'
            ])->find($task->pr_id_fk);

            if ($existingPr) {
                $pr = $existingPr;
                // Group saved items by app_item_id to handle multiple rows per item
                $savedItemsGrouped = $existingPr->prItems->groupBy('pr_app_item_id_fk');
            }
        }

        $breadcrumbs = [
            ['title' => 'Purchase Request', 'url' => route('show.tasks')],
            ['title' => 'Create PR', 'url' => '']
        ];

        // Flag: direct creation means the user created the task themselves from the APP checklist
        $isDirectCreation = ($task->task_type === 'PR Assignment' && $task->assigned_by === $task->assigned_to);

        return match ($userRole) {
            'Head'             => view('head/pages/head-create-pr', compact('task', 'groupedItems', 'pr', 'savedItemsGrouped', 'breadcrumbs', 'isDirectCreation')),
            'Procurement'      => view('procurement/pages/procurement-create-pr', compact('task', 'groupedItems', 'pr', 'savedItemsGrouped', 'breadcrumbs', 'isDirectCreation')),
            'Supply'           => view('supply/pages/supply-create-pr', compact('task', 'groupedItems', 'pr', 'savedItemsGrouped', 'breadcrumbs', 'isDirectCreation')),
            null, 'Unassigned' => view('unassigned/pages/unassigned-create-pr', compact('task', 'groupedItems', 'pr', 'savedItemsGrouped', 'breadcrumbs', 'isDirectCreation')),
            default            => abort(403),
        };
    }

    /**
     * Build validation rules & messages based on intent.
     * Submit = strict (all required). Draft = lenient.
     */
    private function validatePr(Request $request, string $intent, Task $task): \Illuminate\Validation\Validator
    {
        if ($intent === 'submit') {
            $rules = [
                'pr_section'              => 'required|string|min:5|max:50',
                'pr_purpose'              => 'required|string|min:5|max:50',
                'pr_no'                   => 'nullable|string|min:5|max:20',
                'items'                   => 'required|array|min:1',
                'items.*.unit'            => 'required|string|min:1|max:20',
                'items.*.description'     => 'required|string|min:5|max:50',
                'items.*.quantity'        => 'required|integer|min:1|max:9999999',
                'items.*.cost'            => 'required|numeric|min:1|max:9999999',
                'items.*.specification'   => 'nullable|string|min:5|max:500',
            ];
        } else {
            $rules = [
                'pr_section'              => 'nullable|string|min:5|max:50',
                'pr_purpose'              => 'nullable|string|min:5|max:50',
                'pr_no'                   => 'nullable|string|min:5|max:20',
                'items'                   => 'nullable|array',
                'items.*.unit'            => 'nullable|string|min:1|max:20',
                'items.*.description'     => 'nullable|string|min:5|max:50',
                'items.*.quantity'        => 'nullable|integer|min:1|max:9999999',
                'items.*.cost'            => 'nullable|numeric|min:1|max:9999999',
                'items.*.specification'   => 'nullable|string|min:5|max:500',
            ];
        }

        $messages = [
            'pr_section.required'          => 'Section is required.',
            'pr_section.min'               => 'Section must be at least 5 characters.',
            'pr_section.max'               => 'Section must not exceed 50 characters.',
            'pr_purpose.required'          => 'Purpose is required.',
            'pr_purpose.min'               => 'Purpose must be at least 5 characters.',
            'pr_purpose.max'               => 'Purpose must not exceed 50 characters.',
            'pr_no.min'                    => 'PR No. must be at least 5 characters.',
            'pr_no.max'                    => 'PR No. must not exceed 20 characters.',
            'items.required'               => 'At least one item is required.',
            'items.min'                    => 'At least one item is required.',
            'items.*.unit.required'        => 'Unit is required.',
            'items.*.unit.max'             => 'Unit must not exceed 20 characters.',
            'items.*.description.required' => 'Description is required.',
            'items.*.description.min'      => 'Description must be at least 5 characters.',
            'items.*.description.max'      => 'Description must not exceed 50 characters.',
            'items.*.quantity.required'    => 'Quantity is required.',
            'items.*.quantity.integer'     => 'Quantity must be a whole number.',
            'items.*.quantity.min'         => 'Quantity must be at least 1.',
            'items.*.quantity.max'         => 'Quantity is too large.',
            'items.*.cost.required'        => 'Unit cost is required.',
            'items.*.cost.numeric'         => 'Unit cost must be a number.',
            'items.*.cost.min'             => 'Unit cost must be at least 1.',
            'items.*.cost.max'             => 'Unit cost is too large.',
            'items.*.specification.min'    => 'Specification must be at least 5 characters.',
            'items.*.specification.max'    => 'Specification must not exceed 500 characters.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // Add custom budget limits validation hook
        $validator->after(function ($validator) use ($request, $task) {
            $budgetLimit = $task->appItems->sum('app_items_esti_budget');
            $totalAmount = 0;
            $items = $request->input('items', []);

            foreach ($items as $index => $row) {
                if (empty($row['description']) && empty($row['quantity'])) {
                    continue;
                }
                $qty = (int) ($row['quantity'] ?? 0);
                $cost = (float) ($row['cost'] ?? 0);
                $totalAmount += $qty * $cost;

                if ($cost > $budgetLimit) {
                    $validator->errors()->add("items.{$index}.cost", "The unit cost exceeds the allocated budget of PHP " . number_format($budgetLimit, 2));
                }
            }

            if ($totalAmount > $budgetLimit) {
                $validator->errors()->add("general_budget", "The total amount of the Purchase Request (PHP " . number_format($totalAmount, 2) . ") exceeds the allocated budget of PHP " . number_format($budgetLimit, 2));
            }
        });

        return $validator;
    }

    /**
     * Save or update the PR as a Draft.
     * Task status stays "Pending".
     */
    public function saveDraft(Request $request, $task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        $this->authorizeTask($task);

        $pr = $task->pr_id_fk ? PrParent::find($task->pr_id_fk) : null;
        if ($task->task_status !== 'Pending' && (!$pr || $pr->pr_status !== 'Draft')) {
            return response()->json([
                'success' => false,
                'message' => 'This PR has already been submitted and can no longer be edited.',
            ], 409);
        }

        // Validate (lenient for drafts)
        $validator = $this->validatePr($request, 'draft', $task);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $user, $task) {
                $pr = $this->saveOrUpdatePr($request, $user, $task, 'Draft');

                // Link PR to task if not already linked
                if (!$task->pr_id_fk) {
                    $task->update(['pr_id_fk' => $pr->pr_id]);
                }
                // Ensure task status is 'Pending' for drafts
                if ($task->task_status !== 'Pending') {
                    $task->update(['task_status' => 'Pending']);
                }
            });

            session()->flash('success', 'Purchase Request saved as draft.');

            return response()->json([
                'success'  => true,
                'message'  => 'Purchase Request saved as draft.',
                'redirect' => route('show.create.pr', $task_id),
            ]);
        } catch (\Exception $e) {
            Log::error('Draft Save Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while saving the draft. Please try again.',
            ], 500);
        }
    }

    /**
     * Complete the PR (assigned subordinate flow).
     * Task and PR status change to "Complete".
     * No PR Review task is created; the Head views the completed task directly.
     */
    public function submitPr(Request $request, $task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        $this->authorizeTask($task);

        $pr = $task->pr_id_fk ? PrParent::find($task->pr_id_fk) : null;
        if ($task->task_status !== 'Pending' && (!$pr || $pr->pr_status !== 'Draft')) {
            return response()->json([
                'success' => false,
                'message' => 'This PR has already been completed.',
            ], 409);
        }

        // Validate (strict for completion)
        $validator = $this->validatePr($request, 'submit', $task);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $user, $task) {

                // Save/update the PR data first
                $pr = $this->saveOrUpdatePr($request, $user, $task, 'Complete');

                // Link PR to task if not already linked
                if (!$task->pr_id_fk) {
                    $task->update(['pr_id_fk' => $pr->pr_id]);
                }

                // Mark the task as Complete — the Head views this task directly; no review task needed
                $task->update(['task_status' => 'Complete']);

                // Notify the Head (assigned_by) that the PR has been submitted
                if ($task->assigned_by && $task->assigned_by !== $task->assigned_to) {
                    $submitterName = $user->user_fullname_no_middle ?? 'A team member';
                    \App\Models\Task::create([
                        'assigned_by'      => $task->assigned_to,
                        'assigned_to'      => $task->assigned_by,
                        'task_description' => "{$submitterName} has submitted a Purchase Request.",
                        'created_at'       => now(),
                        'pr_id_fk'         => $pr->pr_id ?? $task->pr_id_fk,
                        'task_type'        => 'PR Submitted',
                        'is_deleted'       => 0,
                        'task_status'      => 'Pending',
                        'task_dep_id_fk'   => $task->task_dep_id_fk,
                    ]);
                }
            });

            session()->flash('success', 'Purchase Request marked as complete.');

            return response()->json([
                'success'  => true,
                'message'  => 'Purchase Request marked as complete.',
                'redirect' => route('show.create.pr', $task_id),
            ]);
        } catch (\Exception $e) {
            Log::error('PR Submit Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while completing the PR. Please try again.',
            ], 500);
        }
    }

    /**
     * Shared helper: create or update a PR record with its items and specs.
     */
    private function saveOrUpdatePr(Request $request, $user, Task $task, string $status): PrParent
    {
        // Resolve the active department ID based on the active role's department context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $departmentId = $activeRole ? $activeRole->role_dep_id_fk : ($user->departments->first()?->dep_id);

        // Check if a PR already exists for this task
        $pr = $task->pr_id_fk ? PrParent::find($task->pr_id_fk) : null;

        // Resolve parent APP from first item associated with the task
        $firstItem = $task->appItems()->first();
        $appId = $firstItem ? $firstItem->app_id_fk : null;
        $app = $appId ? AppParent::find($appId) : null;

        if ($pr) {
            // Self-heal/ensure sequential unique code exists
            $uniqueCode = $pr->pr_unique_code;
            // Self-heal: format older codes that do not have the department ID (which would have less than 3 dashes)
            if (!$uniqueCode || substr_count($uniqueCode, '-') < 3) {
                if ($app && $app->app_unique_code) {
                    $cleanAppCode = str_replace(['APP', '-'], '', $app->app_unique_code);
                    $prCount = PrParent::where('app_id_fk', $appId)->count() + 1;
                    $uniqueCode = 'PR-' . $departmentId . '-' . $cleanAppCode . '-' . str_pad($prCount, 3, '0', STR_PAD_LEFT);
                } else {
                    $lastPr = PrParent::orderBy('pr_id', 'desc')->first();
                    $nextNum = $lastPr ? ($lastPr->pr_id + 1) : 1;
                    $uniqueCode = 'PR-' . $departmentId . '-UNKNOWN-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                }
            }

            // Update existing PR header
            $pr->update([
                'pr_section'           => $request->input('pr_section'),
                'pr_no'                => $request->input('pr_no'),
                'pr_department'        => $departmentId,
                'app_id_fk'            => $appId,
                'pr_unique_code'       => $uniqueCode,
                'pr_purpose'           => $request->input('pr_purpose'),
                'pr_status'            => $status,
                'submitted_at'         => $pr->submitted_at ?: (in_array($status, ['Complete', 'Exported']) ? now() : null),
                'pr_name_of_requestor' => $pr->pr_name_of_requestor ?: $user->user_id,
            ]);

            // Delete old items (cascades to specs via FK)
            $pr->prItems()->delete();
        } else {
            // Generate sequential unique code with department ID prefix (PR-DEPID-YYYYVV-XXX format)
            if ($app && $app->app_unique_code) {
                $cleanAppCode = str_replace(['APP', '-'], '', $app->app_unique_code);
                $prCount = PrParent::where('app_id_fk', $appId)->count() + 1;
                $uniqueCode = 'PR-' . $departmentId . '-' . $cleanAppCode . '-' . str_pad($prCount, 3, '0', STR_PAD_LEFT);
            } else {
                $lastPr = PrParent::orderBy('pr_id', 'desc')->first();
                $nextNum = $lastPr ? ($lastPr->pr_id + 1) : 1;
                $uniqueCode = 'PR-' . $departmentId . '-UNKNOWN-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            }

            // Create new PR header
            $pr = PrParent::create([
                'pr_section'           => $request->input('pr_section'),
                'pr_department'        => $departmentId,
                'app_id_fk'            => $appId,
                'pr_no'                => $request->input('pr_no'),
                'pr_purpose'           => $request->input('pr_purpose'),
                'pr_name_of_requestor' => $user->user_id,
                'saved_by_user_id_fk'  => $user->user_id,
                'pr_unique_code'       => $uniqueCode,
                'pr_status'            => $status,
                'submitted_at'         => in_array($status, ['Complete', 'Exported']) ? now() : null,
            ]);
        }

        // Insert items and specs
        $prTotal = 0;
        foreach ($request->input('items', []) as $row) {

            $appItemId = $row['app_item_id'] ?? null;

            // Skip blank rows or missing app_item_id
            if (!$appItemId || (empty($row['description']) && empty($row['quantity']))) {
                continue;
            }

            $qty  = (int)   ($row['quantity'] ?? 0);
            $cost = (float) ($row['cost']     ?? 0);
            $prTotal += $qty * $cost;

            $prItem = PrItem::create([
                'pr_id_fk'            => $pr->pr_id,
                'pr_app_item_id_fk'   => $appItemId,
                'pr_items_descrip'    => $row['description']  ?? null,
                'pr_items_unit'       => $row['unit']         ?? null,
                'pr_items_quantity'   => $qty,
                'pr_items_cost'       => $cost,
            ]);

            if (!empty($row['specification'])) {
                PrSpec::create([
                    'pr_items_id_fk' => $prItem->pr_items_id,
                    'pr_spec_spec'   => $row['specification'],
                ]);
            }
        }

        // Update the pr_total column with the calculated grand total
        $pr->update(['pr_total' => $prTotal]);

        return $pr;
    }
    /**
     * Cancel a completed PR and return it to Pending/Draft status.
     * Allowed only within 3 days of completion.
     * Only available while the Head has not yet exported the PR (status != 'Exported').
     */
    public function cancelPr($task_id)
    {
        $user = Auth::user();
        $task = Task::with('purchaseRequest')->findOrFail($task_id);

        $this->authorizeTask($task);

        $pr = $task->purchaseRequest;

        if (!$pr || $task->task_status !== 'Complete') {
            return redirect()->back()->with('error', 'Only completed purchase requests can be cancelled.');
        }

        // Check 3-day deadline from when it was completed (submitted_at tracks this)
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
            });

            return redirect()->route('show.create.pr', $task_id)
                ->with('success', 'Purchase Request cancelled. It is now back to Draft.');
        } catch (\Exception $e) {
            Log::error('PR Cancel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while cancelling the submission.');
        }
    }

    /**
     * Export the PR.
     * - Assigned flow: task must be 'Complete'; saves edits, sets status → 'Exported'.
     * - Self-created Head flow: task must be 'Pending'; saves and sets status → 'Complete'
     *   (the blade treats 'Complete' on a self-created task as the exported/locked state).
     */
    public function exportPr(Request $request, $task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        $this->authorizeTask($task);

        // Determine flow: self-created = Head assigned the task to themselves
        $isSelfCreated = ($task->assigned_by === $task->assigned_to);

        // Guard: assigned tasks must be Complete; self-created tasks must be Pending
        $allowedStatus = $isSelfCreated ? 'Pending' : 'Complete';
        if ($task->task_status !== $allowedStatus) {
            return response()->json([
                'success' => false,
                'message' => $isSelfCreated
                    ? 'Only pending self-created purchase requests can be exported directly.'
                    : 'Only completed purchase requests can be exported.',
            ], 400);
        }

        // Validate (strict for export)
        $validator = $this->validatePr($request, 'submit', $task);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // Assigned → 'Exported'; Self-created → 'Complete' (blade shows this as "Exported")
            $finalPrStatus   = $isSelfCreated ? 'Complete'  : 'Exported';
            $finalTaskStatus = $isSelfCreated ? 'Complete'  : 'Exported';

            DB::transaction(function () use ($request, $user, $task, $finalPrStatus, $finalTaskStatus) {
                // Save/update the PR data with the resolved status
                $pr = $this->saveOrUpdatePr($request, $user, $task, $finalPrStatus);

                // Get Head's designation for the approval field
                $activeRoleId = session('active_role_id');
                $headRole = $user->roles->where('gen_role', 'Head')->first()
                    ?? $user->roles->where('role_id', $activeRoleId)->first()
                    ?? $user->roles->first();
                $designation = $headRole?->role_name ?? 'Department Head';

                // Stamp the approval
                $pr->update([
                    'pr_approved_by'              => $user->user_id,
                    'pr_approved_by_designation'  => $designation,
                ]);

                // Link PR to task if not already linked
                if (!$task->pr_id_fk) {
                    $task->update(['pr_id_fk' => $pr->pr_id]);
                }

                // Finalize the task status
                $task->update(['task_status' => $finalTaskStatus]);
            });

            session()->flash('success', 'Purchase Request exported and locked.');

            return response()->json([
                'success'      => true,
                'message'      => 'Purchase Request exported successfully.',
                'redirect'     => route('show.create.pr', $task_id),
                'download_url' => route('export.pr.download', $task_id),
            ]);
        } catch (\Exception $e) {
            Log::error('PR Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while exporting the PR. Please try again.',
            ], 500);
        }
    }

    /**
     * Download the Exported PR as PDF using the Excel template.
     */
    public function downloadPdf($task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        $this->authorizeTask($task);

        // Assigned tasks use 'Exported'; self-created Head tasks use 'Complete' as their final state
        $isSelfCreated = ($task->assigned_by === $task->assigned_to);
        $allowedStatuses = $isSelfCreated ? ['Complete'] : ['Exported'];
        if (!in_array($task->task_status, $allowedStatuses)) {
            abort(403, 'Purchase Request must be exported before download.');
        }

        $pr = PrParent::with(['prItems.prSpecs', 'department', 'requestor', 'approver'])
            ->findOrFail($task->pr_id_fk);

        $templatePath = base_path('procurement_documents/Purchase Request Excel Template (2).xlsx');

        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Excel template not found.');
        }

        try {
            $spreadsheet = IOFactory::load($templatePath);
            $sheet = $spreadsheet->getActiveSheet();

            // 1. General Styling & Page Setup
            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial Narrow');
            $sheet->getPageSetup()->setFitToPage(true);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(1);
            $sheet->getPageSetup()->setPrintArea('A1:G54');
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setHorizontalCentered(true);

            // Equal Margins (0.5 inches on all sides)
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setBottom(0.5);
            $sheet->getPageMargins()->setLeft(0.5);
            $sheet->getPageMargins()->setRight(0.5);

            // 2. Institutional Header Row Height Adjustment (Rows 1-5)
            $headerRowHeights = [
                1 => 10,
                2 => 14,
                3 => 14,
                4 => 14,
                5 => 14,
                6 => 0,   // Remove unnecessary row/border
                7 => 12,  // Blank Gap row
            ];
            foreach ($headerRowHeights as $row => $height) {
                $sheet->getRowDimension($row)->setRowHeight($height);
            }

            // 3. Header Data Mapping (Form Info)
            $sheet->setCellValue('B8', $pr->department->dep_name);
            $sheet->setCellValue('F8', $pr->pr_no);
            $sheet->setCellValue('B9', $pr->pr_section);
            $sheet->setCellValue('F9', \Carbon\Carbon::parse($pr->pr_date)->format('M d, Y'));

            // Set Form Info (A8:G9) styles
            $sheet->getStyle('A8:G9')->getFont()->setSize(11);

            // 4. Items mapping (Row 12-47) - Expanded to match new template
            $currRow = 12;
            $items = $pr->prItems;

            $sheet->getStyle('A12:G47')->getFont()->setSize(10);

            foreach ($items as $item) {
                if ($currRow > 47) break;

                $sheet->setCellValue('A' . $currRow, $item->pr_items_quantity);
                $sheet->setCellValue('B' . $currRow, $item->pr_items_unit);

                // Description + Specs (joined with commas, no wrapping)
                $description = $item->pr_items_descrip;
                if ($item->prSpecs->isNotEmpty()) {
                    $specs = $item->prSpecs->pluck('pr_spec_spec')->join(', ');
                    $description .= ", " . $specs;
                }
                $sheet->setCellValue('C' . $currRow, $description);
                $sheet->getStyle('C' . $currRow)->getAlignment()->setWrapText(false);

                $sheet->setCellValue('E' . $currRow, $item->pr_items_cost);
                $sheet->getStyle('E' . $currRow)->getNumberFormat()->setFormatCode('#,##0.00');

                $sheet->setCellValue('G' . $currRow, "=A{$currRow}*E{$currRow}");
                $sheet->getStyle('G' . $currRow)->getNumberFormat()->setFormatCode('#,##0.00');

                $currRow++;
            }

            // 5. Grand Total Row (Row 48)
            $sheet->setCellValue('F48', '=SUM(G12:G47)');
            $sheet->getStyle('F48')->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('F48')->getFont()->setBold(true);

            // 6. Footer (Rows 50-53)
            $sheet->setCellValue('C50', $pr->pr_purpose ?? 'N/A');

            // Name Formatter Helper
            $formatName = function ($user) {
                if (!$user) return 'N/A';
                $mi = $user->user_middlename ? substr($user->user_middlename, 0, 1) . '.' : '';
                return trim($user->user_firstname . ' ' . $mi . ' ' . $user->user_lastname . ' ' . ($user->user_suffix ?? ''));
            };

            $requestorName = strtoupper($formatName($pr->requestor));
            $departmentHeadName = strtoupper($formatName($pr->approver));

            // Footer names and designations
            $sheet->setCellValue('C52', $requestorName);
            $sheet->setCellValue('D52', $departmentHeadName);
            $sheet->getStyle('C52:D52')->getAlignment()->setWrapText(false)->setShrinkToFit(true);
            $sheet->getStyle('C52:D52')->getFont()->setSize(10)->setBold(false);

            $sheet->setCellValue('C53', $pr->pr_designation ?? 'Section Head');
            $sheet->setCellValue('D53', $pr->pr_approved_by_designation ?? 'Department Head');
            $sheet->getStyle('C53:D53')->getAlignment()->setWrapText(false)->setShrinkToFit(true);
            $sheet->getStyle('C53:D53')->getFont()->setSize(9);

            // 6.1 Unique Code (Row 54)
            $sheet->setCellValue('G54', $pr->pr_unique_code ?? 'N/A');
            $sheet->getStyle('G54')->getFont()->setSize(8);
            $sheet->getStyle('G54')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            // 7. Apply Thick Borders
            $thickStyle = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    ],
                ],
            ];
            $sheet->getStyle('A1:G5')->applyFromArray($thickStyle);   // Header Box (1-5)
            $sheet->getStyle('A8:G9')->applyFromArray($thickStyle);   // Form Info Box (8-9)
            $sheet->getStyle('A11:G47')->applyFromArray($thickStyle); // Items Table (11-47)
            $sheet->getStyle('A48:G48')->applyFromArray($thickStyle); // Total Row (48)
            $sheet->getStyle('A50:G53')->applyFromArray($thickStyle); // Footer (50-53)

            // 8. Final Calculation
            Calculation::getInstance($spreadsheet)->clearCalculationCache();
            $sheet->getCell('F48')->getCalculatedValue();

            // Export to PDF using mPDF
            $pdfWriter = new Mpdf($spreadsheet);
            $pdfWriter->setPreCalculateFormulas(true);
            $filename = ($pr->pr_unique_code ?: 'PR_EXPORT') . ".pdf";

            return response()->streamDownload(function () use ($pdfWriter) {
                $pdfWriter->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('PR PDF Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF. Details: ' . $e->getMessage());
        }
    }
}
