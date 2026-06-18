<?php

namespace App\Services;

use App\Models\PoParent;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PoPdfExportService
{
    /**
     * Populate the Purchase Order template and stream it as a PDF download.
     *
     * @param PoParent $po
     * @return StreamedResponse
     */
    public function export(PoParent $po): StreamedResponse
    {
        $templatePath = base_path('procurement_documents/PO Template.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'Purchase Order Excel Template not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheetByName('PO') ?? $spreadsheet->getActiveSheet();

        // 1. Prevent "Worksheet already assigned" Drawing errors by clearing existing drawings
        $drawings = [];
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $drawings[] = $drawing;
        }
        foreach ($drawings as $drawing) {
            $drawing->setWorksheet(null, true);
        }

        // 2. Page Setup & Font Configurations
        $spreadsheet->getDefaultStyle()->getFont()->setName('Aptos Narrow');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getPageSetup()->setPrintArea('A1:F56');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Center on page
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setVerticalCentered(true);

        // Set Margins to 0.2 on all sides
        $sheet->getPageMargins()->setTop(0.2);
        $sheet->getPageMargins()->setBottom(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Helper for dates formatting
        $formatDate = function ($date) {
            if (!$date) return null;
            try {
                return Carbon::parse($date)->format('F j, Y');
            } catch (\Exception $e) {
                return $date;
            }
        };

        // 3. Populate Header Fields
        $sheet->setCellValue('C4', $po->po_supplier ?: null);
        $sheet->setCellValue('C5', $po->po_address ?: null);
        $sheet->setCellValue('C6', $po->po_tele ?: null);
        $sheet->setCellValue('C7', $po->po_tin ?: null);

        $sheet->setCellValue('F4', $po->po_no ?: null);
        $sheet->setCellValue('F5', $formatDate($po->po_date));
        $sheet->setCellValue('F6', $po->po_mode ?: null);
        $sheet->setCellValue('F7', $po->po_tuptin ?: null);

        $sheet->setCellValue('C10', $po->po_place_delivery ?: null);
        $sheet->setCellValue('C11', $formatDate($po->po_date_delivery));
        $sheet->setCellValue('F10', $po->po_delivery_term ?: null);
        $sheet->setCellValue('F11', $po->po_payment_term ?: null);

        // 4. Populate Item Table (Rows 13 to 37)
        $items = $po->poItems;
        $currentRow = 13;
        $maxRow = 37;

        foreach ($items as $item) {
            if ($currentRow > $maxRow) {
                break; // Boundary check: template only supports up to row 37
            }

            $sheet->setCellValue('A' . $currentRow, $item->po_items_stockno ?: null);
            $sheet->setCellValue('B' . $currentRow, $item->po_items_unit ?: null);

            // Description + specs
            $description = $item->po_items_descrip ?: null;
            if ($item->poSpecs && $item->poSpecs->isNotEmpty()) {
                $specs = $item->poSpecs->pluck('po_spec_description')->filter()->implode("\n");
                if ($specs) {
                    $description = ($description ? $description . "\n" : '') . $specs;
                }
            }
            $sheet->setCellValue('C' . $currentRow, $description);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('D' . $currentRow, $item->po_items_quantity ?: null);
            $sheet->setCellValue('E' . $currentRow, $item->po_items_cost ?: null);
            $sheet->setCellValue('F' . $currentRow, $item->po_items_total ?: null);

            $currentRow++;
        }

        // Add "*** Nothing follows ***" marker after the last item if space allows
        if ($currentRow <= $maxRow) {
            $sheet->setCellValue('C' . $currentRow, '*** Nothing follows ***');
            $sheet->getStyle('C' . $currentRow)->getFont()->setBold(true);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $currentRow++;
        }

        // 5. Totals & Summary Section
        // A38: Composite Title Line [po_title] - [pr_department] - [po_fund_cluster]
        $depName = $po->purchaseRequest->department->dep_name ?? $po->purchaseRequest->pr_department ?? null;
        $titleParts = array_filter([$po->po_title, $depName, $po->po_fund_cluster]);
        $compositeTitle = !empty($titleParts) ? implode(' - ', $titleParts) : null;
        $sheet->setCellValue('A38', $compositeTitle);

        // A39: Total in Words
        $amountInWords = null;
        if ($po->po_total_amount) {
            $amountInWords = $this->convertNumberToWords($po->po_total_amount) . ' PESOS ONLY';
        }
        $sheet->setCellValue('A39', $amountInWords);

        // F39: Grand Total numeric value
        $sheet->setCellValue('F39', $po->po_total_amount ?: null);

        // 6. Signatory: Campus Director (Role ID 68)
        $campusDirector = User::whereHas('roles', function ($query) {
            $query->where('roles_tbl.role_id', 68);
        })->first();

        $approverName = $campusDirector ? $campusDirector->user_fullname : 'Engr. REXMELLE F. DECAPIA, JR. Ph.D.';
        $approverDesignation = $campusDirector
            ? ($campusDirector->roles->where('role_id', 68)->first()?->role_name ?? 'Campus Director')
            : 'Campus Director';

        $sheet->setCellValue('D44', strtoupper($approverName));
        $sheet->setCellValue('D45', $approverDesignation);
        $sheet->setCellValue('F56', $po->po_unique_code);

        // 7. Manually Inject TUP Logo
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

        // 8. Generate and Stream PDF
        Calculation::getInstance($spreadsheet)->clearCalculationCache();

        $pdfWriter = new Mpdf($spreadsheet);
        $pdfWriter->setPreCalculateFormulas(true);

        $filename = 'Purchase_Order_' . ($po->po_no ?? $po->po_unique_code) . '.pdf';

        return response()->streamDownload(function () use ($pdfWriter) {
            $pdfWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Convert numeric value to words representation.
     *
     * @param float|int $number
     * @return string
     */
    private function convertNumberToWords($number): string
    {
        if (!extension_loaded('intl')) {
            return strtoupper((string) $number);
        }
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);

        $wholeNumber = floor($number);
        $decimal = round($number - $wholeNumber, 2) * 100;

        $words = strtoupper($formatter->format($wholeNumber));

        if ($decimal > 0) {
            $words .= " AND " . $decimal . "/100";
        }

        return $words;
    }
}
