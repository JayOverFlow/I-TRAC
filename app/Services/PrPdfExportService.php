<?php

namespace App\Services;

use App\Models\PrParent;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrPdfExportService
{
    /**
     * Populate the Purchase Request template and stream it as a PDF download.
     *
     * @param PrParent $pr
     * @return StreamedResponse
     */
    public function export(PrParent $pr): StreamedResponse
    {
        $templatePath = base_path('procurement_documents/PR Template.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'PR Excel Template not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('PR') ?? $spreadsheet->getActiveSheet();

        // Remove the logo render (all drawings/images) to prevent overlaps/layout issues in PDF
        $drawings = [];
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $drawings[] = $drawing;
        }
        foreach ($drawings as $drawing) {
            $drawing->setWorksheet(null, true);
        }

        // 1. General Page Setup
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial Narrow');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getPageSetup()->setPrintArea('A1:G54');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Center page layout
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setVerticalCentered(true);

        // Set Margins sides to 0.2
        $sheet->getPageMargins()->setTop(0.2);
        $sheet->getPageMargins()->setBottom(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Date formatter helper
        $formatDate = function ($date) {
            if (!$date) return '';
            try {
                return Carbon::parse($date)->format('M d, Y');
            } catch (\Exception $e) {
                return $date;
            }
        };

        // 2. Header Section
        $sheet->setCellValue('B8', $pr->department->dep_name);
        $sheet->setCellValue('B9', $pr->pr_section);
        $sheet->setCellValue('F8', $pr->pr_no);
        $sheet->setCellValue('F9', $formatDate($pr->pr_date));

        // 3. Item Table Section (Rows 12 to 47)
        $items = $pr->prItems;
        $currentRow = 12;

        foreach ($items as $item) {
            if ($currentRow > 47) {
                break; // Boundary check: template only supports up to row 47
            }

            // Write Quantity & Unit
            $sheet->setCellValue('A' . $currentRow, $item->pr_items_quantity ?? 0);
            $sheet->setCellValue('B' . $currentRow, $item->pr_items_unit ?? '');

            // Description + Specs
            $description = $item->pr_items_descrip ?? '';
            if ($item->prSpecs && $item->prSpecs->isNotEmpty()) {
                $specs = $item->prSpecs->pluck('pr_spec_spec')->filter()->implode(', ');
                if ($specs) {
                    $description .= ', ' . $specs;
                }
            }
            $sheet->setCellValue('C' . $currentRow, $description);

            // Unit Cost
            $sheet->setCellValue('E' . $currentRow, $item->pr_items_cost ?? 0.00);

            // Total Cost Formula (Quantity * Unit Cost)
            $sheet->setCellValue('G' . $currentRow, "=A{$currentRow}*E{$currentRow}");

            $currentRow++;
        }

        // 4. Totals (Row 48)
        $sheet->setCellValue('F48', '=SUM(G12:G47)');

        // 5. Footer Section
        $sheet->setCellValue('C50', $pr->pr_purpose);

        // Requestor Names and Designation (C52 & C53)
        $requestor = $pr->requestor;
        $requestorName = $requestor->user_fullname;
        $requestorDesignation = '';
        if ($requestor) {
            $requestorDesignation = $requestor->roles->first()?->role_name 
                ?? $requestor->departments->first()?->dep_name 
                ?? '';
        }

        $sheet->setCellValue('C52', strtoupper($requestorName));
        $sheet->setCellValue('C53', $requestorDesignation);

        // Preparer Names and Designation (D52 & D53)
        $preparer = $pr->savedBy;
        $preparerName = $preparer->user_fullname;
        $preparerDesignation = $preparer ? ($preparer->roles->first()?->role_name ?? '') : '';

        $sheet->setCellValue('D52', strtoupper($preparerName));
        $sheet->setCellValue('D53', $preparerDesignation);

        // Approver / Campus Director (E52 & E53)
        // Find user with Campus Director role (ID 68)
        $campusDirectorUser = User::whereHas('roles', function ($query) {
            $query->where('roles_tbl.role_id', 68);
        })->first();

        $approverName = $campusDirectorUser ? $campusDirectorUser->user_fullname : 'Engr. REXMELLE F. DECAPIA, JR. Ph.D.';
        $approverDesignation = $campusDirectorUser 
            ? ($campusDirectorUser->roles->where('role_id', 68)->first()?->role_name ?? 'Campus Director') 
            : 'Campus Director';

        $sheet->setCellValue('E52', strtoupper($approverName));
        $sheet->setCellValue('E53', $approverDesignation);

        // Unique Code (G54)
        $sheet->setCellValue('G54', $pr->pr_unique_code);

        // Inject TUP Logo
        $logoPath = public_path('img/tup-logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('TUP Logo');
            $drawing->setDescription('TUP Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(70);
            $drawing->setOffsetX(15);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }

        // Clear calculations and save to PDF using native mPDF writer
        Calculation::getInstance($spreadsheet)->clearCalculationCache();

        $pdfWriter = new Mpdf($spreadsheet);
        $pdfWriter->setPreCalculateFormulas(true);

        $filename = 'PR_' . str_replace('-', '_', $pr->pr_unique_code ?: $pr->pr_id) . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter) {
            $pdfWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
