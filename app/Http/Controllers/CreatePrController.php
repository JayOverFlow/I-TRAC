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
use App\Services\PrPdfExportService;

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
                'purchaseOrders.poItems',
                'purchaseOrders.iarReports',
                'purchaseOrders.risSlips.risItems',
                'purchaseOrders.icsSlips',
                'purchaseOrders.parReceipts.parItems'
            ])->find($task->pr_id_fk);

            if ($existingPr) {
                $pr = $existingPr;
                // Group saved items by app_item_id to handle multiple rows per item
                $savedItemsGrouped = $existingPr->prItems->groupBy('pr_app_item_id_fk');
            }
        }

        if (request('from') === 'app' && request('app_id')) {
            $breadcrumbs = [
                ['title' => 'Account Settings', 'url' => route('account.settings')],
                ['title' => 'View APP', 'url' => route('show.create-app', ['app_id' => request('app_id'), 'mode' => 'pr'])],
                ['title' => 'Create PR', 'url' => '']
            ];
        } else {
            $breadcrumbs = [
                ['title' => 'Purchase Request', 'url' => route('show.tasks')],
                ['title' => 'Create PR', 'url' => '']
            ];
        }

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
     * Lightweight JSON endpoint polled by the frontend stepper every 30 s.
     * Returns only the data needed to redraw the stepper; no HTML rendered.
     */
    public function stepperStatus($task_id)
    {
        $task = Task::with(['appItems'])->findOrFail($task_id);
        $this->authorizeTask($task);

        if (!$task->pr_id_fk) {
            return response()->json(['steps' => [], 'latestActiveIndex' => -1]);
        }

        $pr = PrParent::with([
            'prItems',
            'purchaseOrders.poItems',
            'purchaseOrders.iarReports',
            'purchaseOrders.risSlips.risItems',
            'purchaseOrders.icsSlips',
            'purchaseOrders.parReceipts.parItems',
        ])->find($task->pr_id_fk);

        if (!$pr) {
            return response()->json(['steps' => [], 'latestActiveIndex' => -1]);
        }

        // PO Coverage
        $firstPo    = $pr->purchaseOrders->first();
        $totalPrQty = $pr->prItems->sum('pr_items_quantity');
        $totalPoQty = $pr->purchaseOrders->flatMap(fn($po) => $po->poItems)->sum('po_items_quantity');
        $poCoverage = 'none';

        if ($pr->purchaseOrders->isNotEmpty()) {
            $poCoverage = ($totalPrQty > 0 && $totalPoQty >= $totalPrQty) ? 'full' : 'partial';
        }

        // Date helpers
        $prReceivedDate = null;
        if ($pr->retrieved_by) {
            $prReceivedDate = $pr->retrieved_at ? \Carbon\Carbon::parse($pr->retrieved_at)->format('d M, Y') : null;
        }

        $poCreatedDate       = $firstPo ? \Carbon\Carbon::parse($firstPo->created_at ?? $firstPo->po_date)->format('d M, Y') : null;
        $isDelivered         = $pr->da_exported_at !== null;
        $deliveryDate        = $pr->da_exported_at ? \Carbon\Carbon::parse($pr->da_exported_at)->format('d M, Y') : null;
        $isReceivedByEndUser = $pr->scanned_at !== null;
        $receivedDate        = $pr->scanned_at ? \Carbon\Carbon::parse($pr->scanned_at)->format('d M, Y') : null;

        $daSubsteps = [];
        if ($pr) {
            foreach ($pr->purchaseOrders as $po) {
                foreach ($po->risSlips as $ris) {
                    $risPoItemIds = $ris->risItems->pluck('ris_po_items_id_fk')->filter();
                    if ($risPoItemIds->isNotEmpty()) {
                        $mrItems = \App\Models\Mr::whereIn('po_item_id_fk', $risPoItemIds)->get();
                        if ($mrItems->isNotEmpty()) {
                            $isAllScanned = $mrItems->every(fn($item) => $item->is_assigned == 1);
                            $hasAnyScanned = $mrItems->contains(fn($item) => $item->is_assigned == 1);
                            
                            $latestScanDate = null;
                            if ($isAllScanned) {
                                $latestScan = $mrItems->whereNotNull('date_scanned')->max('date_scanned');
                                $latestScanDate = $latestScan ? \Carbon\Carbon::parse($latestScan)->format('d M, Y') : null;
                            }

                            $daSubsteps[] = [
                                'prefix'  => 'RIS No. ' . ($ris->ris_no ?? $ris->ris_id),
                                'label'   => $isAllScanned ? 'Completed' : 'Pending',
                                'active'  => (bool) ($isAllScanned || $hasAnyScanned),
                                'partial' => (bool) (!$isAllScanned && $hasAnyScanned),
                                'date'    => $latestScanDate,
                            ];
                        }
                    }
                }

                foreach ($po->parReceipts as $par) {
                    $parPoItemIds = $par->parItems->pluck('par_po_items_id_fk')->filter();
                    if ($parPoItemIds->isNotEmpty()) {
                        $mrItems = \App\Models\Mr::whereIn('po_item_id_fk', $parPoItemIds)->get();
                        if ($mrItems->isNotEmpty()) {
                            $isAllScanned = $mrItems->every(fn($item) => $item->is_assigned == 1);
                            $hasAnyScanned = $mrItems->contains(fn($item) => $item->is_assigned == 1);
                            
                            $latestScanDate = null;
                            if ($isAllScanned) {
                                $latestScan = $mrItems->whereNotNull('date_scanned')->max('date_scanned');
                                $latestScanDate = $latestScan ? \Carbon\Carbon::parse($latestScan)->format('d M, Y') : null;
                            }

                            $daSubsteps[] = [
                                'prefix'  => 'PAR No. ' . ($par->par_property_no ?? $par->par_id),
                                'label'   => $isAllScanned ? 'Completed' : 'Pending',
                                'active'  => (bool) ($isAllScanned || $hasAnyScanned),
                                'partial' => (bool) (!$isAllScanned && $hasAnyScanned),
                                'date'    => $latestScanDate,
                            ];
                        }
                    }
                }
            }
        }

        $isAnyScanned = collect($daSubsteps)->contains('active', true);

        $isSelfCreated = ($task->task_type === 'PR Assignment' && $task->assigned_by === $task->assigned_to);

        $steps = [
            [
                'prefix'  => 'Purchase Request:',
                'label'   => 'CREATED',
                'active'  => (bool) ($pr->pr_status === 'Exported' || ($isSelfCreated && $pr->pr_status === 'Complete')),
                'partial' => false,
                'date'    => ($pr->submitted_at ?? $pr->updated_at ?? $pr->created_at)
                    ? \Carbon\Carbon::parse($pr->submitted_at ?? $pr->updated_at ?? $pr->created_at)->format('d M, Y')
                    : null,
            ],
            [
                'prefix'  => 'Purchase Request:',
                'label'   => 'RECEIVED BY PROCUREMENT OFFICE',
                'active'  => (bool) $pr->retrieved_by,
                'partial' => false,
                'date'    => $prReceivedDate,
            ],
            [
                'prefix'  => 'Purchase Order:',
                'label'   => ($pr->is_po_done == 0 && $pr->purchaseOrders->isNotEmpty()) ? 'PARTIALLY CREATED' : 'CREATED',
                'active'  => (bool) ($pr->is_po_done == 1 || $pr->purchaseOrders->isNotEmpty()),
                'partial' => (bool) ($pr->is_po_done == 0 && $pr->purchaseOrders->isNotEmpty()),
                'date'    => $pr->po_done_at ? \Carbon\Carbon::parse($pr->po_done_at)->format('d M, Y') : null,
                'sub_steps' => $pr->purchaseOrders->map(fn($po) => [
                    'prefix'  => $po->po_title,
                    'label'   => $po->po_status === 'Done' ? 'Completed' : 'Draft',
                    'active'  => true,
                    'partial' => $po->po_status !== 'Done',
                    'date'    => $po->po_status === 'Done' && $po->updated_at ? \Carbon\Carbon::parse($po->updated_at)->format('d M, Y') : null,
                ])->toArray(),
            ],
            [
                'prefix'  => 'Purchase Order:',
                'label'   => ($pr->da_exported_at === null && $pr->purchaseOrders->contains(fn($po) => $po->hasAnyDaExported())) ? 'PARTIALLY DELIVERED' : 'DELIVERED',
                'active'  => (bool) ($pr->da_exported_at !== null || $pr->purchaseOrders->contains(fn($po) => $po->hasAnyDaExported())),
                'partial' => (bool) ($pr->da_exported_at === null && $pr->purchaseOrders->contains(fn($po) => $po->hasAnyDaExported())),
                'date'    => $deliveryDate,
                'sub_steps' => $pr->purchaseOrders->map(fn($po) => [
                    'prefix'  => $po->po_title,
                    'label'   => $po->is_da_exported == 1 ? 'Completed' : 'Pending',
                    'active'  => (bool) ($po->is_da_exported == 1 || $po->hasAnyDaExported()),
                    'partial' => (bool) ($po->is_da_exported == 0 && $po->hasAnyDaExported()),
                    'date'    => $po->is_da_exported == 1 && $po->updated_at ? \Carbon\Carbon::parse($po->updated_at)->format('d M, Y') : null,
                ])->toArray(),
            ],
            [
                'prefix'  => 'Purchase Order:',
                'label'   => ($pr->scanned_at === null && $isAnyScanned) ? 'PARTIALLY RECEIVED BY END USER' : 'RECEIVED ITEM BY END USER',
                'active'  => (bool) ($pr->scanned_at !== null || $isAnyScanned),
                'partial' => (bool) ($pr->scanned_at === null && $isAnyScanned),
                'date'    => $receivedDate,
                'sub_steps' => $daSubsteps,
            ],
        ];

        $latestActiveIndex = -1;
        foreach ($steps as $i => $step) {
            if ($step['active']) {
                $latestActiveIndex = $i;
            }
        }

        return response()->json([
            'steps'             => $steps,
            'latestActiveIndex' => $latestActiveIndex,
        ]);
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
                'items.*.specification'   => 'nullable|string|min:5|max:250',
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
                'items.*.specification'   => 'nullable|string|min:5|max:250',
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
            'items.*.specification.max'    => 'Specification must not exceed 250 characters.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // Add custom budget limits validation hook
        $validator->after(function ($validator) use ($request, $task, $intent) {
            $budgetLimit = $task->appItems->sum('app_items_esti_budget');
            $totalAmount = 0;
            $items = $request->input('items', []);

            $user = Auth::user();
            $isAssigned = ($task->assigned_by !== $task->assigned_to);
            $isViewer_Assigner = ($task->assigned_by === $user->user_id);
            $canHeadEdit = $isViewer_Assigner && $isAssigned && ($task->task_status === 'Complete') && (!$task->pr_id_fk || ($task->purchaseRequest && $task->purchaseRequest->pr_status === 'Complete'));

            $originalItems = collect();
            if ($canHeadEdit && $task->pr_id_fk) {
                $originalItems = \App\Models\PrItem::where('pr_id_fk', $task->pr_id_fk)
                    ->with('prSpecs')
                    ->get()
                    ->keyBy('pr_items_id');
            }

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

                // Validate remarks for the Head's edit view when exporting (submit)
                if ($intent === 'submit' && $canHeadEdit) {
                    $prItemId = $row['pr_item_id'] ?? null;
                    $originalItem = $prItemId ? $originalItems->get($prItemId) : null;

                    $isModified = false;
                    if ($originalItem) {
                        // Compare description
                        if (($row['description'] ?? '') !== ($originalItem->pr_items_descrip ?? '')) {
                            $isModified = true;
                        }
                        // Compare unit
                        if (($row['unit'] ?? '') !== ($originalItem->pr_items_unit ?? '')) {
                            $isModified = true;
                        }
                        // Compare quantity
                        if (isset($row['quantity']) && (int)$row['quantity'] !== (int)($originalItem->pr_items_quantity ?? 0)) {
                            $isModified = true;
                        }
                        // Compare cost
                        if (isset($row['cost']) && abs((float)$row['cost'] - (float)($originalItem->pr_items_cost ?? 0.0)) > 0.001) {
                            $isModified = true;
                        }
                        // Compare specification
                        $origSpecText = $originalItem->prSpecs->first()?->pr_spec_spec ?? '';
                        if (($row['specification'] ?? '') !== $origSpecText) {
                            $isModified = true;
                        }
                    } else {
                        // Newly added item is considered a modification
                        $isModified = true;
                    }

                    if ($isModified && empty($row['remarks'])) {
                        $validator->errors()->add("items.{$index}.remarks", "Remarks are required when modifying or adding this item.");
                    }
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
            // Self-heal: format older codes that do not have the 3-digit department ID
            $parts = explode('-', (string)$uniqueCode);
            if (!$uniqueCode || count($parts) < 4 || strlen($parts[1]) !== 3) {
                if ($app && $app->app_unique_code) {
                    $cleanAppCode = str_replace(['APP', '-'], '', $app->app_unique_code);
                    $prCount = PrParent::where('app_id_fk', $appId)->count() + 1;
                    $uniqueCode = 'PR-' . str_pad($departmentId, 3, '0', STR_PAD_LEFT) . '-' . $cleanAppCode . '-' . str_pad($prCount, 3, '0', STR_PAD_LEFT);
                } else {
                    $lastPr = PrParent::orderBy('pr_id', 'desc')->first();
                    $nextNum = $lastPr ? ($lastPr->pr_id + 1) : 1;
                    $uniqueCode = 'PR-' . str_pad($departmentId, 3, '0', STR_PAD_LEFT) . '-UNKNOWN-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
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
            // Generate sequential unique code with department ID prefix (PR-000-000000-000 format)
            if ($app && $app->app_unique_code) {
                $cleanAppCode = str_replace(['APP', '-'], '', $app->app_unique_code);
                $prCount = PrParent::where('app_id_fk', $appId)->count() + 1;
                $uniqueCode = 'PR-' . str_pad($departmentId, 3, '0', STR_PAD_LEFT) . '-' . $cleanAppCode . '-' . str_pad($prCount, 3, '0', STR_PAD_LEFT);
            } else {
                $lastPr = PrParent::orderBy('pr_id', 'desc')->first();
                $nextNum = $lastPr ? ($lastPr->pr_id + 1) : 1;
                $uniqueCode = 'PR-' . str_pad($departmentId, 3, '0', STR_PAD_LEFT) . '-UNKNOWN-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
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
                'remarks'             => $row['remarks']      ?? null,
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

                // Check if any item contains remarks (indicating head revision)
                $hasRemarks = false;
                foreach ($request->input('items', []) as $row) {
                    if (!empty($row['remarks'])) {
                        $hasRemarks = true;
                        break;
                    }
                }

                // If this is an assigned task and changes were made, dispatch notification to subordinate
                $isSelfCreated = ($task->assigned_by === $task->assigned_to);
                if (!$isSelfCreated && $hasRemarks) {
                    $headName = $user->user_fullname_no_middle ?? 'The Department Head';
                    \App\Models\Task::create([
                        'assigned_by'      => $user->user_id,
                        'assigned_to'      => $task->assigned_to,
                        'task_description' => "{$headName} has revised your Purchase Request.",
                        'created_at'       => now(),
                        'pr_id_fk'         => $pr->pr_id ?? $task->pr_id_fk,
                        'task_type'        => 'PR Revised',
                        'is_deleted'       => 0,
                        'task_status'      => 'Pending',
                        'task_dep_id_fk'   => $task->task_dep_id_fk,
                    ]);
                }

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

        $pr = PrParent::with(['prItems.prSpecs', 'department', 'requestor.roles', 'requestor.departments', 'savedBy.roles', 'approver'])
            ->findOrFail($task->pr_id_fk);

        try {
            $pdfService = app(PrPdfExportService::class);
            return $pdfService->export($pr);
        } catch (\Exception $e) {
            Log::error('PR PDF Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF. Details: ' . $e->getMessage());
        }
    }
}
