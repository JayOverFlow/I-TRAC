<?php

namespace App\Services;

use App\Models\Rsmi;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RsmiPdfExportService
{
    /**
     * Populate the RSMI template and stream it as a PDF download.
     *
     * @param Rsmi $rsmi
     * @return StreamedResponse
     */
    public function export(Rsmi $rsmi): StreamedResponse
    {
        $templatePath = base_path('procurement_documents/RSMI Template.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'RSMI Excel Template not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('RSMI') ?? $spreadsheet->getActiveSheet();

        // Prevent "Worksheet already assigned" Drawing errors by clearing existing drawings
        $drawings = [];
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $drawings[] = $drawing;
        }
        foreach ($drawings as $drawing) {
            $drawing->setWorksheet(null, true);
        }

        // 1. General Page Setup
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getPageSetup()->setPrintArea('A1:I53');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Center page
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setVerticalCentered(true);

        // Set Margins
        $sheet->getPageMargins()->setTop(0.2);
        $sheet->getPageMargins()->setBottom(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Helper for formatting date
        $formatDate = function ($date) {
            if (!$date) return '';
            try {
                return Carbon::parse($date)->format('m/d/Y');
            } catch (\Exception $e) {
                return $date;
            }
        };

        // 2. Header Section
        $sheet->setCellValue('B7', $rsmi->rsmi_fund_cluster ?? '');
        $sheet->setCellValueExplicit('H7', $rsmi->rsmi_serial_no ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B8', $rsmi->rsmi_po_no ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('H8', $formatDate($rsmi->rsmi_date));

        // 3. Item Table (Rows 11 to 40)
        $items = $rsmi->rsmiItems;
        $currentRow = 11;

        foreach ($items as $item) {
            if ($currentRow > 40) {
                break; // Hard limit reached
            }

            // RIS No.
            $sheet->setCellValueExplicit('A' . $currentRow, $item->rsmi_ris_no ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // Responsibility Center Code
            $sheet->setCellValueExplicit('B' . $currentRow, $item->rsmi_center_code ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // Stock No.
            $sheet->setCellValueExplicit('C' . $currentRow, $item->rsmi_stock_no ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // Merge Columns D to E programmatically
            $sheet->mergeCells("D{$currentRow}:E{$currentRow}");

            // Combine Description and Specifications
            $description = $item->rsmi_items_descrip;
            if ($item->rsmiSpecs && $item->rsmiSpecs->isNotEmpty()) {
                $specs = $item->rsmiSpecs->pluck('rsmi_spec_description')->filter()->implode("\n");
                if ($specs) {
                    $description .= "\n" . $specs;
                }
            }
            $sheet->setCellValue('D' . $currentRow, $description ?? '');
            $sheet->getStyle('D' . $currentRow)->getAlignment()->setWrapText(true);

            // Unit
            $sheet->setCellValue('F' . $currentRow, $item->rsmi_unit ?? '');

            // Quantity Issued
            $sheet->setCellValue('G' . $currentRow, $item->rsmi_quantity ?? '');

            // Unit Cost
            $sheet->setCellValue('H' . $currentRow, $item->rsmi_unit_cost ?? '');

            // Amount
            $sheet->setCellValue('I' . $currentRow, $item->rsmi_amount ?? ($item->rsmi_quantity * $item->rsmi_unit_cost));

            $currentRow++;
        }

        // 4. Recapitulation Table (Rows 44 to 50)
        // Kept empty/null per design requirement

        // 5. Signatories Section
        // A51 contains the Supply Officer / User name
        $sheet->setCellValue('A51', strtoupper($rsmi->user->user_fullname ?? ''));

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

        $filename = 'RSMI_' . str_replace('-', '_', $rsmi->rsmi_serial_no ?: $rsmi->rsmi_id) . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter) {
            $pdfWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
