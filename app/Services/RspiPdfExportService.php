<?php

namespace App\Services;

use App\Models\Rspi;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RspiPdfExportService
{
    /**
     * Populate the RSPI template and stream it as a PDF download.
     *
     * @param Rspi $rspi
     * @return StreamedResponse
     */
    public function export(Rspi $rspi): StreamedResponse
    {
        $templatePath = base_path('procurement_documents/RSPI Template.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'RSPI Excel Template not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('RSPI') ?? $spreadsheet->getActiveSheet();

        // 1. General Page Setup
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getPageSetup()->setPrintArea('A1:H44');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Horizontally and Vertically center on page
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setVerticalCentered(true);

        // Set Margins
        $sheet->getPageMargins()->setTop(0.2);
        $sheet->getPageMargins()->setBottom(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Helper for dates formatting
        $formatDate = function ($date) {
            if (!$date) return '';
            try {
                return Carbon::parse($date)->format('m/d/Y');
            } catch (\Exception $e) {
                return $date;
            }
        };

        // Helper to resolve designation
        $getDesignation = function ($user, $storedDesignation) {
            if (!empty($storedDesignation)) {
                return $storedDesignation;
            }
            if (!$user) {
                return '';
            }
            return $user->roles->first()?->role_name ?? $user->user_type ?? '';
        };

        // 2. Header Section
        $sheet->setCellValue('B8', $rspi->rspi_fund_cluster ?? '');
        $sheet->setCellValue('B9', $rspi->rspi_po_no ?? '');
        $sheet->setCellValue('H8', $rspi->rspi_serial_no ?? '');
        $sheet->setCellValue('H9', $formatDate($rspi->rspi_date));

        // 3. Item Table (Rows 12 to 38)
        $items = $rspi->rspiItems;
        $currentRow = 12;

        foreach ($items as $item) {
            if ($currentRow > 38) {
                break; // Hard limit reached (max 27 rows)
            }

            // Write ICS No
            $sheet->setCellValue('A' . $currentRow, $item->rspi_ics_no ?? '');

            // Write Center Code
            $sheet->setCellValue('B' . $currentRow, $item->rspi_center_code ?? '');

            // Write Property No
            $sheet->setCellValue('C' . $currentRow, $item->rspi_property_no ?? '');

            // Combine Description and Specifications
            $description = $item->rspi_items_descrip;
            if ($item->rspiSpecs && $item->rspiSpecs->isNotEmpty()) {
                $specs = $item->rspiSpecs->pluck('rspi_spec_description')->filter()->implode("\n");
                if ($specs) {
                    $description .= "\n" . $specs;
                }
            }
            $sheet->setCellValue('D' . $currentRow, $description ?? '');
            $sheet->getStyle('D' . $currentRow)->getAlignment()->setWrapText(true);

            // Write Unit
            $sheet->setCellValue('E' . $currentRow, $item->rspi_unit ?? '');

            // Write Quantity
            $sheet->setCellValue('F' . $currentRow, $item->rspi_quantity ?? '');

            // Write Unit Cost
            $sheet->setCellValue('G' . $currentRow, $item->rspi_unit_cost ?? '');

            // Write Amount
            $amount = $item->rspi_amount;
            if ($amount === null || $amount == 0) {
                $amount = ($item->rspi_quantity ?? 0) * ($item->rspi_unit_cost ?? 0);
            }
            $sheet->setCellValue('H' . $currentRow, $amount);

            $currentRow++;
        }

        // 4. Signatories Section
        $sheet->setCellValue('A43', $rspi->user ? $rspi->user->user_fullname : '');
        $sheet->setCellValue('A44', $getDesignation($rspi->user, $rspi->rspi_designation));

        // Clear calculations and save to PDF using native mPDF writer
        Calculation::getInstance($spreadsheet)->clearCalculationCache();

        $pdfWriter = new Mpdf($spreadsheet);
        $pdfWriter->setPreCalculateFormulas(true);

        $filename = 'RSPI_' . str_replace('-', '_', $rspi->rspi_serial_no ?: $rspi->rspi_id) . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter) {
            $pdfWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
