<?php

namespace App\Services;

use App\Models\Mr;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryReportExportService
{
    /**
     * Generate the Inventory Report PDF.
     *
     * @param array $filters
     * @return StreamedResponse
     */
    /**
     * Get count of filtered items matching the given filters.
     *
     * @param array $filters
     * @return int
     */
    public function getFilteredItemsCount(array $filters): int
    {
        $query = Mr::select('mr_tbl.mr_id');
        $this->applyFilters($query, $filters);
        return $query->count();
    }

    /**
     * Apply common filters and joins to the query.
     */
    protected function applyFilters($query, array $filters)
    {
        $query->leftJoin('par_items_tbl as orig_par_item', 'mr_tbl.par_item_id_fk', '=', 'orig_par_item.par_items_id')
            ->leftJoin('par_tbl as orig_par', 'orig_par_item.par_id_fk', '=', 'orig_par.par_id')
            ->leftJoin('ris_items_tbl as orig_ris_item', 'mr_tbl.ris_item_id_fk', '=', 'orig_ris_item.ris_items_id')
            ->leftJoin('ris_tbl as orig_ris', 'orig_ris_item.ris_id_fk', '=', 'orig_ris.ris_id')
            ->leftJoin('ics_tbl as t_ics', 't_ics.mr_id_fk', '=', 'mr_tbl.mr_id')
            ->leftJoin('par_tbl as t_par', function($join) {
                $join->on('t_par.mr_id_fk', '=', 'mr_tbl.mr_id')
                     ->where('t_par.is_transfer', '=', 1);
            });

        // Period filter
        $year = $filters['filter_year'] ?? date('Y');
        $period = $filters['reporting_period'] ?? 'Annual';

        $coalesceDateStr = 'COALESCE(
            t_ics.ics_received_by_date,
            t_ics.created_at,
            t_par.par_received_by_date,
            t_par.created_at,
            orig_par.par_received_by_date,
            orig_par.created_at,
            orig_ris.ris_received_date,
            orig_ris.created_at,
            mr_tbl.created_at
        )';

        if ($period === 'Monthly' && !empty($filters['filter_month'])) {
            $month = intval($filters['filter_month']);
            $query->whereRaw("YEAR({$coalesceDateStr}) = ?", [$year])
                  ->whereRaw("MONTH({$coalesceDateStr}) = ?", [$month]);
        } elseif ($period === 'Quarterly' && !empty($filters['filter_quarter'])) {
            $quarter = intval($filters['filter_quarter']);
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $quarter * 3;
            $query->whereRaw("YEAR({$coalesceDateStr}) = ?", [$year])
                  ->whereRaw("MONTH({$coalesceDateStr}) BETWEEN ? AND ?", [$startMonth, $endMonth]);
        } else {
            $query->whereRaw("YEAR({$coalesceDateStr}) = ?", [$year]);
        }

        // Category filter
        if (!empty($filters['filter_category']) && $filters['filter_category'] !== 'All') {
            $query->where('mr_tbl.category', $filters['filter_category']);
        }

        // Grouping & specificity filter
        $groupBy = $filters['filter_group_by'] ?? 'user';
        if ($groupBy === 'user') {
            if (!empty($filters['filter_user'])) {
                $query->where('mr_tbl.assigned_to', $filters['filter_user']);
            }
        } elseif ($groupBy === 'office') {
            if (!empty($filters['filter_office'])) {
                $officeId = $filters['filter_office'];
                $query->whereHas('assignedUser.departments', function($q) use ($officeId) {
                    $q->where('departments_tbl.dep_id', $officeId);
                });
            }
        }
    }

    /**
     * Generate the Inventory Report PDF.
     *
     * @param array $filters
     * @return StreamedResponse
     */
    public function export(array $filters): StreamedResponse
    {
        // 1. Build Query
        $query = Mr::select('mr_tbl.*')
            ->selectRaw('COALESCE(
                t_ics.ics_received_by_date,
                t_ics.created_at,
                t_par.par_received_by_date,
                t_par.created_at,
                orig_par.par_received_by_date,
                orig_par.created_at,
                orig_ris.ris_received_date,
                orig_ris.created_at,
                mr_tbl.created_at
            ) as delivery_date')
            ->with(['assignedUser.departments', 'poItem', 'risItem.poItem', 'parItem.poItem']);

        $this->applyFilters($query, $filters);

        $items = $query->orderBy('delivery_date', 'desc')->get();

        // Resolve labels for metadata headers
        $year = $filters['filter_year'] ?? date('Y');
        $period = $filters['reporting_period'] ?? 'Annual';
        if ($period === 'Monthly' && !empty($filters['filter_month'])) {
            $month = intval($filters['filter_month']);
            $monthName = Carbon::create()->month($month)->format('F');
            $periodText = "Monthly - {$monthName} {$year}";
        } elseif ($period === 'Quarterly' && !empty($filters['filter_quarter'])) {
            $quarter = intval($filters['filter_quarter']);
            $periodText = "Quarterly - Q{$quarter} {$year}";
        } else {
            $periodText = "Annual - {$year}";
        }

        $categoryText = "All Categories";
        if (!empty($filters['filter_category']) && $filters['filter_category'] !== 'All') {
            $categoryText = $filters['filter_category'];
        }

        $groupByText = "All Users & Offices";
        $groupBy = $filters['filter_group_by'] ?? 'user';
        if ($groupBy === 'user') {
            if (!empty($filters['filter_user'])) {
                $user = User::find($filters['filter_user']);
                if ($user) {
                    $groupByText = "Per End-User: " . $user->user_fullname;
                }
            } else {
                $groupByText = "Per End-User (All)";
            }
        } elseif ($groupBy === 'office') {
            if (!empty($filters['filter_office'])) {
                $officeId = $filters['filter_office'];
                $office = Department::find($officeId);
                if ($office) {
                    $acronymPart = $office->dep_acronym ? " ({$office->dep_acronym})" : "";
                    $groupByText = "Per Office: " . $office->dep_name . $acronymPart;
                }
            } else {
                $groupByText = "Per Office (All)";
            }
        }

        // 2. Create Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory Report');

        // Page setup: Landscape, A4, fit to width
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setVerticalCentered(false);

        // Margin setup
        $sheet->getPageMargins()->setTop(0.4);
        $sheet->getPageMargins()->setBottom(0.4);
        $sheet->getPageMargins()->setLeft(0.4);
        $sheet->getPageMargins()->setRight(0.4);

        // General Font
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Inject logo
        $logoPath = public_path('img/tup-logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('TUP Logo');
            $drawing->setDescription('TUP Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('C1');
            $drawing->setHeight(50);
            $drawing->setOffsetX(20);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }

        // Header Labels
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A4:J4');

        $sheet->setCellValue('A1', 'TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES');
        $sheet->setCellValue('A2', 'Taguig');
        $sheet->setCellValue('A4', 'INVENTORY REPORT');

        $sheet->getStyle('A1:J1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2:J2')->getFont()->setSize(10);
        $sheet->getStyle('A4:J4')->getFont()->setBold(true)->setSize(15)->getColor()->setARGB('FF8C0404');

        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:J2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:J4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Metadata Info
        $sheet->setCellValue('A6', 'Reporting Period:');
        $sheet->setCellValue('B6', $periodText);
        $sheet->setCellValue('F6', 'Item Category:');
        $sheet->setCellValue('G6', $categoryText);

        $sheet->setCellValue('A7', 'Grouped By:');
        $sheet->setCellValue('B7', $groupByText);
        $sheet->setCellValue('F7', 'Date Generated:');
        $sheet->setCellValue('G7', Carbon::now()->format('F d, Y h:i A'));

        $sheet->getStyle('A6:A7')->getFont()->setBold(true);
        $sheet->getStyle('F6:F7')->getFont()->setBold(true);

        // Table Header
        $headers = [
            'A9' => 'Item Name',
            'B9' => 'Specification',
            'C9' => 'Qty',
            'D9' => 'Unit',
            'E9' => 'Category',
            'F9' => 'Assigned User',
            'G9' => 'Office',
            'H9' => 'Date Received',
            'I9' => 'Unit Cost',
            'J9' => 'Total Cost'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRange = 'A9:J9';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF8C0404');
        
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(9)->setRowHeight(25);

        // Fill Items
        $row = 10;
        foreach ($items as $item) {
            // Resolve unit cost
            $unitCost = 0;
            if ($item->poItem) {
                $unitCost = $item->poItem->po_items_cost;
            } elseif ($item->parItem) {
                $unitCost = $item->parItem->poItem->po_items_cost ?? $item->parItem->par_amount ?? 0;
            } elseif ($item->risItem) {
                $unitCost = $item->risItem->poItem->po_items_cost ?? 0;
            }
            $unitCost = floatval($unitCost);
            $qty = intval($item->quantity ?? 0);
            $totalCost = $qty * $unitCost;

            // Resolve Office acronym
            $officeAcronym = 'N/A';
            if ($item->assignedUser && $item->assignedUser->departments->isNotEmpty()) {
                $dep = $item->assignedUser->departments->first();
                $officeAcronym = $dep->dep_acronym ?: $dep->dep_name ?: 'N/A';
            }

            $sheet->setCellValue("A{$row}", $item->item_name ?? '');
            $sheet->setCellValue("B{$row}", $item->specification ?? '');
            $sheet->setCellValue("C{$row}", $qty);
            $sheet->setCellValue("D{$row}", $item->unit ?? '');
            $sheet->setCellValue("E{$row}", $item->category ?? '');
            $sheet->setCellValue("F{$row}", $item->assignedUser ? $item->assignedUser->user_fullname : 'N/A');
            $sheet->setCellValue("G{$row}", $officeAcronym);
            $sheet->setCellValue("H{$row}", $item->delivery_date ? Carbon::parse($item->delivery_date)->format('Y-m-d') : '—');
            $sheet->setCellValue("I{$row}", $unitCost);
            $sheet->setCellValue("J{$row}", $totalCost);

            // Row styles & formatting
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('"₱"#,##0.00');
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('"₱"#,##0.00');

            // Zebra Striping
            if ($row % 2 == 1) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9FAFB');
            }

            $row++;
        }

        $lastDataRow = $row - 1;

        // If no items, make lastDataRow 9 so we don't crash
        if ($lastDataRow < 10) {
            $lastDataRow = 9;
            $sheet->setCellValue("A10", "No scanned items found matching current filters.");
            $sheet->mergeCells("A10:J10");
            $sheet->getStyle("A10:J10")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A10:J10")->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF6B7280'));
            $row = 11;
        }

        // Grand Total Row
        $sheet->setCellValue("A{$row}", 'Grand Total');
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
        if ($lastDataRow >= 10) {
            $sheet->setCellValue("C{$row}", "=SUM(C10:C{$lastDataRow})");
            $sheet->setCellValue("J{$row}", "=SUM(J10:J{$lastDataRow})");
        } else {
            $sheet->setCellValue("C{$row}", 0);
            $sheet->setCellValue("J{$row}", 0);
        }

        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('"₱"#,##0.00');

        $totalRange = "A{$row}:J{$row}";
        $sheet->getStyle($totalRange)->getFont()->setBold(true);
        
        // Borders
        $thinBorderColor = 'FFE5E7EB';
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => $thinBorderColor],
                ],
            ],
        ];
        
        $sheet->getStyle("A9:J{$row}")->applyFromArray($borderStyle);

        // Custom borders for total row to look premium (double bottom line)
        $sheet->getStyle($totalRange)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);

        // Adjust column widths manually to fit everything nicely
        $sheet->getColumnDimension('A')->setWidth(22); // Item Name
        $sheet->getColumnDimension('B')->setWidth(30); // Specification
        $sheet->getColumnDimension('C')->setWidth(8);  // Qty
        $sheet->getColumnDimension('D')->setWidth(8);  // Unit
        $sheet->getColumnDimension('E')->setWidth(18); // Category
        $sheet->getColumnDimension('F')->setWidth(22); // Assigned User
        $sheet->getColumnDimension('G')->setWidth(12); // Office
        $sheet->getColumnDimension('H')->setWidth(14); // Date Received
        $sheet->getColumnDimension('I')->setWidth(16); // Unit Cost
        $sheet->getColumnDimension('J')->setWidth(18); // Total Cost

        // Wrap text for description & specs
        $sheet->getStyle("A10:B{$row}")->getAlignment()->setWrapText(true);

        // Set Print Area
        $sheet->getPageSetup()->setPrintArea("A1:J{$row}");

        // Build PDF
        Calculation::getInstance($spreadsheet)->clearCalculationCache();
        $pdfWriter = new Mpdf($spreadsheet);
        $pdfWriter->setPreCalculateFormulas(true);

        $filename = 'Inventory_Report_' . str_replace(' ', '_', $periodText) . '_' . date('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter) {
            $pdfWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
