<?php

namespace App\Services;

use App\Models\Par;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParPdfExportService
{
    /**
     * Populate the PAR template and stream it as a PDF download.
     *
     * @param Par $par
     * @return StreamedResponse
     */
    public function export(Par $par): StreamedResponse
    {
        $templatePath = base_path('procurement_documents/PAR Template.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'PAR Excel Template not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('PAR') ?? $spreadsheet->getActiveSheet();

        // 1. General Page Setup
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getPageSetup()->setPrintArea('A1:F52');
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
            // Check if user has a department first
            if (isset($user->departments) && $user->departments->isNotEmpty()) {
                return $user->departments->first()->dep_name;
            }
            return $user->roles->first()?->role_name ?? $user->user_type ?? '';
        };

        // 2. Header Section
        $sheet->setCellValue('C7', $par->par_fund_cluster ?? '');
        $sheet->setCellValue('C8', $par->par_po_no ?? '');
        $sheet->setCellValue('F7', $par->par_no ?? '');
        $sheet->setCellValue('E8', $par->par_code ?? '');

        // 3. Item Table (Rows 10 to 43)
        $items = $par->parItems;
        $currentRow = 10;

        foreach ($items as $item) {
            if ($currentRow > 43) {
                break; // Hard limit reached (max 34 rows)
            }

            // Write Quantity
            $sheet->setCellValue('A' . $currentRow, $item->par_quantity ?? '');

            // Write Unit
            $sheet->setCellValue('B' . $currentRow, $item->par_unit ?? '');

            // Combine Description and Specifications
            $description = $item->par_items_descrip;
            if ($item->parSpecs && $item->parSpecs->isNotEmpty()) {
                $specs = $item->parSpecs->pluck('par_spec_description')->filter()->implode("\n");
                if ($specs) {
                    $description .= "\n" . $specs;
                }
            }
            $sheet->setCellValue('C' . $currentRow, $description ?? '');
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setWrapText(true);

            // Write Property No
            $sheet->setCellValue('D' . $currentRow, $item->par_property_no ?? '');

            // Write Date Acquired
            $sheet->setCellValue('E' . $currentRow, $formatDate($item->par_date_acquired));

            // Write Amount
            $sheet->setCellValue('F' . $currentRow, $item->par_amount ?? '');

            $currentRow++;
        }

        // 4. Signatories Section
        // Received by (Left block)
        $sheet->setCellValue('A47', $par->receiver ? $par->receiver->user_fullname : '');
        $sheet->setCellValue('A49', $getDesignation($par->receiver, $par->par_received_by_pos));
        $sheet->setCellValue('A51', $formatDate($par->par_received_by_date));

        // Issued by (Right block)
        // D47: overwrite anomalous 'par_issued_by_pos' placeholder with issuer name
        $sheet->setCellValue('D47', $par->issuer ? $par->issuer->user_fullname : 'Supply Officer');
        // D49: write issuer position/office
        $sheet->setCellValue('D48', $getDesignation($par->issuer, $par->par_issued_by_pos) ?: 'Supply Officer');
        // D52: write issue date to align/close the sheet correctly
        $sheet->setCellValue('D50', $formatDate($par->par_issued_by_date));

        // Clear calculations and save to PDF using native mPDF writer
        Calculation::getInstance($spreadsheet)->clearCalculationCache();

        $pdfWriter = new Mpdf($spreadsheet);
        $pdfWriter->setPreCalculateFormulas(true);

        $filename = 'PAR_' . str_replace('-', '_', $par->par_no ?: $par->par_id) . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter) {
            $pdfWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
