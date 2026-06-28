<?php

namespace App\Services;

use App\Models\IarItem;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UbrPdfExportService
{
    /**
     * Populate the UBR template and stream it as a PDF download.
     *
     * @param string $officeName
     * @param string $asOfDate
     * @param \Illuminate\Support\Collection $items
     * @return StreamedResponse
     */
    public function export(string $officeName, string $asOfDate, $items): StreamedResponse
    {
        $templatePath = base_path('procurement_documents/UBR Template.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'UBR Excel Template not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        
        // Use UBR sheet and set it as active
        $sheet = $spreadsheet->getSheetByName('UBR') ?? $spreadsheet->getActiveSheet();
        if ($spreadsheet->sheetNameExists('UBR')) {
            $spreadsheet->setActiveSheetIndexByName('UBR');
        }

        // Prevent "Worksheet already assigned" Drawing errors by clearing existing drawings
        $drawings = [];
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $drawings[] = $drawing;
        }
        foreach ($drawings as $drawing) {
            $drawing->setWorksheet(null, true);
        }

        // Inject TUP Logo manually in A1:A4 area to replace #VALUE! rich-text placeholder
        $logoPath = public_path('img/tup-logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('TUP Logo');
            $drawing->setDescription('TUP Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(65);
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }

        // General Page Setup
        $spreadsheet->getDefaultStyle()->getFont()->setName('Aptos Narrow');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0); // Allow natural vertical page flow
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Center on page
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setVerticalCentered(false);

        // Set Margins
        $sheet->getPageMargins()->setTop(0.2);
        $sheet->getPageMargins()->setBottom(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setRight(0.2);

        // Populate Header Info
        $sheet->setCellValue('B7', $officeName);
        $sheet->setCellValue('E7', $asOfDate);

        // Fill Item Table
        $currentRow = 9;
        $grandTotal = 0;

        foreach ($items as $item) {
            // If we exceed row 52 (which means count > 44), insert new rows dynamically
            if ($currentRow > 52) {
                $sheet->insertNewRowBefore($currentRow, 1);
            }

            // Write values
            $sheet->setCellValue('A' . $currentRow, $item->iar_unit ?? '');
            $sheet->setCellValue('B' . $currentRow, $item->iar_items_descrip ?? '');
            
            $qty = intval($item->iar_quantity ?? 0);
            $unitCost = floatval($item->poItem->po_items_cost ?? 0);
            $totalCost = $qty * $unitCost;
            $grandTotal += $totalCost;

            $sheet->setCellValue('C' . $currentRow, $qty);
            $sheet->setCellValue('D' . $currentRow, $unitCost);
            $sheet->setCellValue('E' . $currentRow, $totalCost);

            // Format number columns
            $sheet->getStyle('D' . $currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E' . $currentRow)->getNumberFormat()->setFormatCode('#,##0.00');

            $currentRow++;
        }

        // Set grand total in D5:E5
        $sheet->setCellValue('D5', $grandTotal);
        // Ensure currency formatting for total
        $sheet->getStyle('D5')->getNumberFormat()->setFormatCode('"₱"#,##0.00');

        // Dynamically adjust print area
        $lastRow = max(52, $currentRow - 1);
        $sheet->getPageSetup()->setPrintArea('A1:E' . $lastRow);

        // Clear calculations and save to PDF using native mPDF writer
        Calculation::getInstance($spreadsheet)->clearCalculationCache();

        $pdfWriter = new Mpdf($spreadsheet);
        $pdfWriter->setPreCalculateFormulas(true);

        $filename = 'Utilized_Budget_Report_' . str_replace(' ', '_', $officeName) . '_' . date('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter) {
            $pdfWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
