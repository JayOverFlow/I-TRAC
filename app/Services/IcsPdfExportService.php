<?php

namespace App\Services;

use App\Models\Ics;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IcsPdfExportService
{
    /**
     * Populate the ICS template and stream it as a PDF download.
     *
     * @param Ics $ics
     * @return StreamedResponse
     */
    public function export(Ics $ics): StreamedResponse
    {
        $templatePath = base_path('procurement_documents/ICS Template.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'ICS Excel Template not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('ICS') ?? $spreadsheet->getActiveSheet();

        // 1. General Page Setup
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getPageSetup()->setPrintArea('A1:H55');
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

        // Helper to resolve designation (uses role name of the user)
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
        $sheet->setCellValue('C7', $ics->ics_fund_cluster ?? '');
        $sheet->setCellValue('C8', $ics->ics_po_no ?? '');
        $sheet->setCellValue('H7', $ics->ics_no ?? '');
        $sheet->setCellValue('G8', $ics->ics_code_no ?? '');

        // 3. Item Table (Rows 11 to 47)
        $items = $ics->icsItems;
        $currentRow = 11;

        foreach ($items as $item) {
            if ($currentRow > 47) {
                break; // Hard limit reached
            }

            // Write Quantity
            $sheet->setCellValue('A' . $currentRow, $item->ics_quantity ?? '');

            // Write Unit
            $sheet->setCellValue('B' . $currentRow, $item->ics_unit ?? '');

            // Write Unit Cost
            $sheet->setCellValue('C' . $currentRow, $item->ics_unit_cost ?? '');

            // Write Total Cost
            $totalCost = $item->ics_total_cost;
            if ($totalCost === null || $totalCost == 0) {
                $totalCost = ($item->ics_quantity ?? 0) * ($item->ics_unit_cost ?? 0);
            }
            $sheet->setCellValue('D' . $currentRow, $totalCost);

            // Merge Columns E to F programmatically
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");

            // Combine Description and Specifications
            $description = $item->ics_items_descrip;
            if ($item->icsSpecs && $item->icsSpecs->isNotEmpty()) {
                $specs = $item->icsSpecs->pluck('ics_spec_description')->filter()->implode("\n");
                if ($specs) {
                    $description .= "\n" . $specs;
                }
            }
            $sheet->setCellValue('E' . $currentRow, $description ?? '');
            $sheet->getStyle('E' . $currentRow)->getAlignment()->setWrapText(true);

            // Write Inventory Item No
            $sheet->setCellValue('G' . $currentRow, $item->ics_inventory_item_no ?? '');

            // Write Estimated Useful Life
            $sheet->setCellValue('H' . $currentRow, $item->ics_estimated_useful_life ?? '');

            $currentRow++;
        }

        // 4. Signatories Section
        // Received From (Giver)
        $sheet->setCellValue('A50', $ics->giver ? $ics->giver->user_fullname : 'Supply Officer');
        $sheet->setCellValue('A51', $getDesignation($ics->giver, $ics->ics_received_from_pos));
        $sheet->setCellValue('A53', $formatDate($ics->ics_received_from_date));

        // Received By (Receiver)
        $sheet->setCellValue('F50', $ics->receiver ? $ics->receiver->user_fullname : '');
        $sheet->setCellValue('F52', $getDesignation($ics->receiver, $ics->ics_received_by_pos));
        $sheet->setCellValue('F54', $formatDate($ics->ics_received_by_date));

        // Clear calculations and save to PDF using native mPDF writer
        Calculation::getInstance($spreadsheet)->clearCalculationCache();

        $pdfWriter = new Mpdf($spreadsheet);
        $pdfWriter->setPreCalculateFormulas(true);

        $filename = 'ICS_' . str_replace('-', '_', $ics->ics_no ?: $ics->ics_id) . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter) {
            $pdfWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
