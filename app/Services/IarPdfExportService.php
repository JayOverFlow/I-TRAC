<?php

namespace App\Services;

use App\Models\Iar;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IarPdfExportService
{
    /**
     * Populate the IAR template and stream it as a PDF download.
     *
     * @param Iar $iar
     * @return StreamedResponse
     */
    public function export(Iar $iar): StreamedResponse
    {
        $templatePath = base_path('procurement_documents/IAR Template.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'IAR Excel Template not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('IAR') ?? $spreadsheet->getActiveSheet();

        // 1. General Page Setup
        $spreadsheet->getDefaultStyle()->getFont()->setName('Aptos Narrow');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getPageSetup()->setPrintArea('A1:F53');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Horizontally and Vertically center on page
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setVerticalCentered(true);

        // Set Margins sides to 0
        $sheet->getPageMargins()->setTop(0);
        $sheet->getPageMargins()->setBottom(0);
        $sheet->getPageMargins()->setLeft(0.8);
        $sheet->getPageMargins()->setRight(0.8);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Helper for dates
        $formatDate = function ($date) {
            if (!$date) return '';
            try {
                return Carbon::parse($date)->format('m/d/Y');
            } catch (\Exception $e) {
                return $date;
            }
        };

        // 2. Header Section
        $sheet->setCellValue('B8', $iar->iar_fund_cluster ?? '');
        $sheet->setCellValue('B9', $iar->iar_supplier ?? '');
        $sheet->setCellValue('F9', $iar->iar_no ?? '');
        $sheet->setCellValue('B10', $iar->iar_po_no ?? '');

        // Extract PO Date from iar_po_no_date or purchaseOrder
        $poDate = '';
        if ($iar->iar_po_no_date && str_contains($iar->iar_po_no_date, ' / ')) {
            $parts = explode(' / ', $iar->iar_po_no_date);
            $poDate = end($parts);
        } else {
            $poDate = $iar->purchaseOrder->po_date ?? '';
        }
        $sheet->setCellValue('C10', $formatDate($poDate));
        $sheet->setCellValue('F10', $formatDate($iar->iar_date));
        $sheet->setCellValue('C11', $iar->iar_office ?? '');
        $sheet->setCellValue('F11', $iar->iar_invoice_no ?? '');
        $sheet->setCellValue('C12', $iar->iar_center_code ?? '');
        $sheet->setCellValue('F12', $formatDate($iar->iar_invoice_date));

        // Merge cells to provide enough width to prevent wrapping/clipping in the PDF
        $sheet->mergeCells('B8:D8');   // Fund Cluster
        $sheet->mergeCells('B9:D9');   // Supplier
        $sheet->mergeCells('C11:D11'); // Requisition Office
        $sheet->mergeCells('A11:B11'); // Requisition Office
        $sheet->mergeCells('A11:B11'); // Requisitioning Office / Dep't.
        $sheet->mergeCells('A12:B12'); // Responsibility Center Code
        $sheet->mergeCells('E48:F48'); // Acceptance tick box

        // Explicitly disable word wrapping for the target cells and set alignments
        foreach (['B8', 'B9', 'F9', 'C11', 'F11'] as $cell) {
            $sheet->getStyle($cell)->getAlignment()->setWrapText(false);
        }
        $sheet->getStyle('B8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('E48')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F11')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Set a slightly smaller font size for F9 and F11 to ensure they fit column F
        $sheet->getStyle('F9')->getFont()->setSize(9);
        $sheet->getStyle('F11')->getFont()->setSize(7);
        $sheet->getStyle('A45')->getFont()->setSize(10);

        // 3. Item Table (Rows 14 to 42)
        $items = $iar->iarItems;

        $currentRow = 14;
        foreach ($items as $item) {
            if ($currentRow > 42) {
                break; // Hard limit reached
            }

            // Write Stock Number
            $sheet->setCellValue('A' . $currentRow, $item->iar_stock_no ?? '');

            // Merge Columns B to D programmatically
            $sheet->mergeCells("B{$currentRow}:D{$currentRow}");

            // Combine Description and Specifications
            $description = $item->iar_items_descrip;
            if ($item->iarSpecs && $item->iarSpecs->isNotEmpty()) {
                $specs = $item->iarSpecs->pluck('iar_spec_description')->filter()->implode("\n");
                if ($specs) {
                    $description .= "\n" . $specs;
                }
            }
            $sheet->setCellValue('B' . $currentRow, $description ?? '');
            $sheet->getStyle('B' . $currentRow)->getAlignment()->setWrapText(true);

            // Write Unit and Quantity
            $sheet->setCellValue('E' . $currentRow, $item->iar_unit ?? '');
            $sheet->setCellValue('F' . $currentRow, $item->iar_quantity ?? 0);

            $currentRow++;
        }

        // 4. Inspection & Acceptance Section
        $sheet->setCellValue('B45', $formatDate($iar->iar_date_inspected));
        $sheet->setCellValue('E45', $formatDate($iar->iar_date_accepted));

        // User requested: "Always tick as Complete"
        $sheet->setCellValue('D47', '☑ Complete');
        $sheet->setCellValue('E47', '☐ Partial');

        // Inspector Full Name
        $inspectedBy = '';
        if ($iar->iar_inspected_by) {
            if (is_numeric($iar->iar_inspected_by)) {
                $inspectorUser = User::find($iar->iar_inspected_by);
                $inspectedBy = $inspectorUser ? $inspectorUser->user_fullname : '';
            } else {
                $inspectedBy = $iar->iar_inspected_by;
            }
        }
        $sheet->setCellValue('A51', strtoupper($inspectedBy));

        // Acceptor Full Name
        $acceptedBy = '';
        if ($iar->iar_accepted_by) {
            if (is_numeric($iar->iar_accepted_by)) {
                $acceptorUser = User::find($iar->iar_accepted_by);
                $acceptedBy = $acceptorUser ? $acceptorUser->user_fullname : '';
            } else {
                $acceptedBy = $iar->iar_accepted_by;
            }
        } else {
            // Default to head of property & supply role (role_id 10)
            $headPropertySupply = User::whereHas('roles', function ($query) {
                $query->where('roles_tbl.role_id', 10);
            })->first();
            $acceptedBy = $headPropertySupply ? $headPropertySupply->user_fullname : 'RONNIE A. RAMOS';
        }
        $sheet->setCellValue('D51', strtoupper($acceptedBy));

        // Clear calculations and save to PDF using native mPDF writer
        Calculation::getInstance($spreadsheet)->clearCalculationCache();

        $pdfWriter = new Mpdf($spreadsheet);
        $pdfWriter->setPreCalculateFormulas(true);

        $filename = 'IAR_' . str_replace('-', '_', $iar->iar_no ?: $iar->iar_id) . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter) {
            $pdfWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
