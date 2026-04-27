<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\PrParent;
use App\Models\PrItem;
use App\Models\PrSpec;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

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
     * Show the edit page for Head to edit submitted PR.
     */
    public function editPrReview($task_id)
    {
        $user = Auth::user();

        $task = Task::findOrFail($task_id);

        if ($task->task_type !== 'PR Review' || $task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $pr = PrParent::with(['prItems.prSpecs', 'prItems.appItem', 'department', 'requestor'])->findOrFail($task->pr_id_fk);

        $savedItemsGrouped = collect();
        if ($pr) {
            $savedItemsGrouped = $pr->prItems->groupBy('pr_app_item_id_fk');
        }

        // Get the grouped APP items via the original PR task
        $originalTask = Task::where('pr_id_fk', $pr->pr_id)
            ->where('task_type', 'Purchase Request')
            ->first();

        $groupedItems = collect();
        if ($originalTask) {
            $originalTask->load('appItems');
            $groupedItems = $originalTask->appItems->groupBy('app_item_proj_title');
        }

        return view('head.pages.head-edit-submitted-pr', compact('task', 'pr', 'groupedItems', 'savedItemsGrouped'));
    }

    /**
     * Update the PR after Head edits it.
     */
    public function updatePrReview(Request $request, $task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        if ($task->task_type !== 'PR Review' || $task->assigned_to !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::transaction(function () use ($request, $task) {
                // Determine the PR
                $pr = PrParent::findOrFail($task->pr_id_fk);

                // Update PR parent data
                $pr->update([
                    'pr_section' => $request->input('pr_section'),
                    'pr_no'      => $request->input('pr_no'),
                    'pr_purpose' => $request->input('pr_purpose'),
                ]);

                // Delete old items and specs to recreate them
                $pr->prItems()->delete();

                // Insert items and specs
                foreach ($request->input('items', []) as $row) {
                    $appItemId = $row['app_item_id'] ?? null;

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
            });

            return redirect()->route('show.pr.review', $task_id)
                ->with('success', 'Purchase Request updated successfully.');
        } catch (\Exception $e) {
            Log::error('Head PR Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the PR. Please try again.');
        }
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

        DB::transaction(function () use ($task, $user) {
            // Update the PR status
            $pr = PrParent::findOrFail($task->pr_id_fk);
            $pr->update([
                'pr_status' => 'Approved',
                'approved_at' => now(),
                'pr_approved_by' => $user->user_id,
                'pr_approved_by_designation' => $user->roles->first()?->gen_role ?? 'Department Head',
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

    /**
     * Export the Approved PR as PDF using the Excel template.
     */
    public function exportPdf($task_id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($task_id);

        if ($task->task_status !== 'Approved') {
            abort(403, 'Purchase Request must be approved before export.');
        }

        $pr = PrParent::with(['prItems.prSpecs', 'department', 'requestor', 'approver'])
            ->findOrFail($task->pr_id_fk);

        $templatePath = base_path('procurement_documents/Purchase Request Excel Template.xlsx');

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
            $sheet->getPageSetup()->setPrintArea('A1:G51');
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setHorizontalCentered(true);

            // Equal Margins (0.5 inches on all sides)
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setBottom(0.5);
            $sheet->getPageMargins()->setLeft(0.5);
            $sheet->getPageMargins()->setRight(0.5);

            // 2. Institutional Header Styles (Rows 1-6)
            $headerStyles = [
                'B2' => 11, // Republic of the Philippines
                'B3' => 12, // TECHNOLOGICAL UNIVERSITY...
                'B4' => 11, // Km. 14 East Service Road...
                'B5' => 10.5, // Website and Phone
                'E2' => 11, // PROCUREMENT OFFICE
                'E3' => 20, // PURCHASE REQUEST
                'E5' => 9,   // REV info
            ];
            foreach ($headerStyles as $cell => $size) {
                $sheet->getStyle($cell)->getFont()->setSize($size);
                $sheet->getStyle($cell)->getAlignment()->setWrapText(false);
            }

            // Prevent header headings from breaking into multiple lines
            $sheet->getStyle('E3')->getAlignment()->setWrapText(false)->setShrinkToFit(true);
            $sheet->getStyle('E3')->getFont()->setSize(20);

            // Adjust institutional header height and remove gap
            $headerRowHeights = [
                1 => 10,
                2 => 13,
                3 => 13,
                4 => 13,
                5 => 12,
                6 => 11,
            ];
            for ($i = 1; $i <= 6; $i++) {
                $sheet->getRowDimension($i)->setRowHeight($headerRowHeights[$i] ?? 12);
            }
            $sheet->getRowDimension(7)->setRowHeight(0); // Remove gap under header

            // 3. Header Data Mapping
            $sheet->setCellValue('B8', $pr->department->dep_name ?? 'N/A');
            $sheet->setCellValue('F8', $pr->pr_no ?? 'N/A');
            $sheet->setCellValue('B9', $pr->pr_section ?? 'N/A');
            $sheet->setCellValue('F9', $pr->pr_date ? \Carbon\Carbon::parse($pr->pr_date)->format('M d, Y') : 'N/A');

            // 4. Items mapping (Row 12-45)
            $currRow = 12;
            $items = $pr->prItems;

            $sheet->getStyle('A12:G45')->getFont()->setSize(12);

            foreach ($items as $item) {
                if ($currRow > 45) break;

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

            // 5. Total Mapping
            $sheet->getStyle('A46:G46')->getFont()->setSize(14)->setBold(true);
            // NOTE: In the template, the grand total cell is typically a merged region (F46:G46).
            // Always write formulas to the top-left cell of a merged region.
            $sheet->setCellValue('F46', '=SUM(G12:G45)');
            $sheet->getStyle('F46')->getNumberFormat()->setFormatCode('#,##0.00');

            // 6. Footer (Rows 47-51)
            $sheet->getStyle('A47:G51')->getFont()->setSize(11);
            $sheet->setCellValue('C48', $pr->pr_purpose ?? 'N/A');

            // Name Formatter Helper
            $formatName = function ($user) {
                if (!$user) return 'N/A';
                $mi = $user->user_middlename ? substr($user->user_middlename, 0, 1) . '.' : '';
                return trim($user->user_firstname . ' ' . $mi . ' ' . $user->user_lastname . ' ' . ($user->user_suffix ?? ''));
            };

            $requestorName = strtoupper($formatName($pr->requestor));
            $departmentHeadName = strtoupper($formatName($pr->approver));

            // Expected layout (based on PDF example):
            // - C50: Requestor name
            // - D50: Department Head name (if template has a dedicated cell; safe to write even if merged)
            $sheet->setCellValue('C50', $requestorName);
            $sheet->setCellValue('D50', $departmentHeadName);
            $sheet->getStyle('C50:D50')->getAlignment()->setWrapText(false)->setShrinkToFit(true);
            $sheet->getStyle('C50:D50')->getFont()->setSize(10);

            // NAME OF REQUESTOR label (readonly in template) — enforce single-line rendering
            $sheet->getStyle('A50')->getAlignment()->setWrapText(false)->setShrinkToFit(true);

            $sheet->setCellValue('C51', $pr->pr_designation ?? 'Section Head');
            $sheet->setCellValue('D51', $pr->pr_approved_by_designation ?? 'Department Head');
            $sheet->getStyle('C51:D51')->getAlignment()->setWrapText(false)->setShrinkToFit(true);
            $sheet->getStyle('C51:D51')->getFont()->setSize(10);

            // Director line (ensure single line)
            $sheet->setCellValue('E50', 'Engr. REXMELLE F. DECAPIA, JR. Ph.D.');
            $sheet->getStyle('E50:G50')->getAlignment()->setWrapText(false)->setShrinkToFit(true);
            $sheet->getStyle('E50:G50')->getFont()->setSize(9.5);

            // 7. Apply Thick Borders
            $thickStyle = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    ],
                ],
            ];
            $sheet->getStyle('A1:G6')->applyFromArray($thickStyle);   // Header
            $sheet->getStyle('A11:G45')->applyFromArray($thickStyle); // Items Table
            $sheet->getStyle('A46:G46')->applyFromArray($thickStyle); // Total Row
            $sheet->getStyle('A47:G51')->applyFromArray($thickStyle); // Footer

            // Export to PDF using mPDF
            // Force formula calculation so PDF renders numeric totals (not blank/uncomputed)
            Calculation::getInstance($spreadsheet)->clearCalculationCache();
            $sheet->getCell('F46')->getCalculatedValue();

            $pdfWriter = new Mpdf($spreadsheet);
            $pdfWriter->setPreCalculateFormulas(true);
            $filename = "PR_" . str_replace('-', '_', $pr->pr_no ?: 'EXPORT') . ".pdf";

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
