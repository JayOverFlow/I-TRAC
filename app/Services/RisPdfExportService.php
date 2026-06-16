<?php

namespace App\Services;

use App\Models\Ris;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RisPdfExportService
{
    /**
     * Populate the RIS template and stream it as a PDF download.
     *
     * @param Ris $ris
     * @return StreamedResponse
     */
    public function export(Ris $ris): StreamedResponse
    {
        $templatePath = base_path('procurement_documents/RIS Template.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'RIS Excel Template not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('RIS') ?? $spreadsheet->getActiveSheet();

        // 1. General Page Setup
        $spreadsheet->getDefaultStyle()->getFont()->setName('Aptos Narrow');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);

        // Check if the RIS is Semi-Expendable and set print area/retrieve QR code
        $firstItem = $ris->risItems->first();
        $isSemiExpendable = $firstItem && $firstItem->poItem && $firstItem->poItem->po_items_category === 'Semi-Expendable';

        $printArea = $isSemiExpendable ? 'A1:I62' : 'A1:I61';
        $sheet->getPageSetup()->setPrintArea($printArea);

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

        // Helper to resolve designation (if user has no role, render user type instead)
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
        $sheet->setCellValue('C7', $ris->ris_fund_cluster ?? '');
        $sheet->setCellValue('B8', $ris->ris_division ?? '');
        $sheet->setCellValue('I8', $ris->ris_center_code ?? '');
        $sheet->setCellValue('B9', $ris->ris_office ?? '');
        $sheet->setCellValue('G9', $ris->ris_no ?? '');

        // 3. Item Table (Rows 12 to 54)
        $items = $ris->risItems;
        $currentRow = 12;

        foreach ($items as $item) {
            if ($currentRow > 54) {
                break; // Hard limit reached
            }

            // Write Stock Number
            $sheet->setCellValue('A' . $currentRow, $item->ris_stock_no ?? '');

            // Write Unit
            $sheet->setCellValue('B' . $currentRow, $item->ris_unit ?? '');

            // Merge Columns C to D programmatically
            $sheet->mergeCells("C{$currentRow}:D{$currentRow}");

            // Combine Description and Specifications
            $description = $item->ris_items_descrip;
            if ($item->risSpecs && $item->risSpecs->isNotEmpty()) {
                $specs = $item->risSpecs->pluck('ris_spec_description')->filter()->implode("\n");
                if ($specs) {
                    $description .= "\n" . $specs;
                }
            }
            $sheet->setCellValue('C' . $currentRow, $description ?? '');
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setWrapText(true);

            // Write Quantity Requisitioned
            $sheet->setCellValue('E' . $currentRow, $item->ris_quantity ?? '');

            // Stock Available (Yes/No) columns F and G
            if ($item->ris_stock_available === 'Yes') {
                $sheet->setCellValue('F' . $currentRow, 'X');
                $sheet->setCellValue('G' . $currentRow, '');
            } elseif ($item->ris_stock_available === 'No') {
                $sheet->setCellValue('F' . $currentRow, '');
                $sheet->setCellValue('G' . $currentRow, 'X');
            } else {
                $sheet->setCellValue('F' . $currentRow, '');
                $sheet->setCellValue('G' . $currentRow, '');
            }

            // Write Issued Quantity & Remarks
            $sheet->setCellValue('H' . $currentRow, $item->ris_issued_quantity ?? '');
            $sheet->setCellValue('I' . $currentRow, $item->ris_issued_remarks ?? '');

            $currentRow++;
        }

        // 4. Purpose Section (Row 55)
        $sheet->setCellValue('B55', $ris->ris_purpose ?? '');

        // 5. Signatories Section
        // Requested By
        $sheet->setCellValue('C59', $ris->requester->user_fullname ?? '');
        $sheet->setCellValue('C60', $getDesignation($ris->requester, $ris->ris_requested_designation));
        $sheet->setCellValue('C61', $formatDate($ris->ris_requested_date));

        // Approved By (No default - null if empty)
        $sheet->setCellValue('D59', $ris->approver->user_fullname ?? '');
        $sheet->setCellValue('D60', $getDesignation($ris->approver, $ris->ris_approved_designation));
        $sheet->setCellValue('D61', $formatDate($ris->ris_approved_date));

        // Issued By (No default - null if empty)
        $sheet->setCellValue('E59', $ris->issuer->user_fullname ?? '');
        $sheet->setCellValue('E60', $getDesignation($ris->issuer, $ris->ris_issued_designation));
        $sheet->setCellValue('E61', $formatDate($ris->ris_issued_date));

        // Received By
        $sheet->setCellValue('H59', $ris->receiver->user_fullname ?? '');
        $sheet->setCellValue('H60', $getDesignation($ris->receiver, $ris->ris_received_designation));
        $sheet->setCellValue('H61', $formatDate($ris->ris_received_date));

        // 6. Generate and Embed QR Code if Semi-Expendable
        $tempImage = null;
        if ($isSemiExpendable) {
            $poItemIds = $ris->risItems->pluck('ris_po_items_id_fk')->filter();
            if ($poItemIds->isNotEmpty()) {
                $mrEntry = \App\Models\Mr::whereIn('po_item_id_fk', $poItemIds)->first();
                if ($mrEntry && $mrEntry->mr_qr_code) {
                    $options = new \chillerlan\QRCode\QROptions([
                        'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                        'scale'           => 10,
                        'imageTransparent' => false,
                    ]);
                    $qrcode = new \chillerlan\QRCode\QRCode($options);
                    $tempImage = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
                    $qrcode->render($mrEntry->mr_qr_code, $tempImage);

                    // Add Drawing to Sheet
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('MR QR Code');
                    $drawing->setDescription('QR Code for MR Assignment');
                    $drawing->setPath($tempImage);
                    $drawing->setCoordinates('A62');
                    $drawing->setHeight(110);
                    $drawing->setOffsetX(15);
                    $drawing->setOffsetY(10);
                    $drawing->setWorksheet($sheet);
                }
            }
        }

        // Clear calculations and save to PDF using native mPDF writer
        Calculation::getInstance($spreadsheet)->clearCalculationCache();

        $pdfWriter = new Mpdf($spreadsheet);
        $pdfWriter->setPreCalculateFormulas(true);

        $filename = 'RIS_' . str_replace('-', '_', $ris->ris_no ?: $ris->ris_id) . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter, $tempImage) {
            $pdfWriter->save('php://output');
            if ($tempImage && file_exists($tempImage)) {
                @unlink($tempImage);
            }
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
