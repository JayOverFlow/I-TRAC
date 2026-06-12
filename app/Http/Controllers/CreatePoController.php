<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PoParent;
use App\Models\PoItem;
use App\Models\PoSpec;
use App\Models\PrParent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CreatePoController extends Controller
{
    public function showCreatePo($po_id) {
        $po = PoParent::with(['poItems.poSpecs'])->findOrFail($po_id);

        $poItems = $po->poItems;

        // Ensure the staging area ('Add Items' card) always has at least one row for interactivity
        if ($poItems->isEmpty()) {
            $poItems = collect([new PoItem()]);
        }

        $breadcrumbs = [
            ['title' => 'Procurement', 'url' => route('show.procure')],
            ['title' => 'PR Preview', 'url' => route('show.pr.preview', $po->pr_id_fk)],
            ['title' => 'Create PO', 'url' => '']
        ];

        return view('procurement/pages/procurement-create-po', compact('po', 'poItems', 'breadcrumbs'));
    }

    public function createPo(Request $request, $pr_id) {
        $request->validate([
            'po_title' => 'required|string|max:45',
        ]);

        $user = Auth::user();

        // Resolve parent PR
        $pr = PrParent::findOrFail($pr_id);

        // Generate sequential unique code (PO-[Clean PR Code]-[PO Count] format)
        if ($pr && $pr->pr_unique_code) {
            $cleanPrCode = str_replace(['PR', '-'], '', $pr->pr_unique_code);
            $poCount = PoParent::where('pr_id_fk', $pr_id)->count() + 1;
            $uniqueCode = 'PO-' . $cleanPrCode . '-' . str_pad($poCount, 3, '0', STR_PAD_LEFT);
        } else {
            $lastPo = PoParent::orderBy('po_id', 'desc')->first();
            $nextNum = $lastPo ? ($lastPo->po_id + 1) : 1;
            $uniqueCode = 'PO-UNKNOWN-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        $po = PoParent::create([
            'pr_id_fk' => $pr_id,
            'po_title' => $request->po_title,
            'po_unique_code' => $uniqueCode,
            'saved_by_user_id_fk' => $user->user_id,
            'po_date' => now()->toDateString(),
            'po_status' => 'Draft',
        ]);

        return redirect()->route('show.create.po', ['po_id' => $po->po_id])
            ->with('success', 'Purchase Order created successfully.');
    }

    /**
     * Build validation rules & messages based on intent.
     * Done = strict (all required). Draft = lenient (nullable).
     */
    private function validatePo(Request $request, string $intent): \Illuminate\Validation\Validator
    {
        if ($intent === 'Done') {
            $rules = [
                'po_supplier'         => 'required|string|min:5|max:100',
                'po_address'          => 'required|string|min:5|max:200',
                'po_tele'             => 'required|string|min:5|max:60',
                'po_tin'              => 'required|string|regex:/^\d{3}-\d{3}-\d{3}-\d{3}$/',
                'po_place_delivery'   => 'required|string|min:5|max:100',
                'po_date_delivery'    => 'required|date',
                'po_no'               => 'required|string|min:5|max:50',
                'po_date'             => 'required|date',
                'po_mode'             => 'required|string|min:5|max:50',
                'po_tuptin'           => 'required|string|regex:/^\d{3}-\d{3}-\d{3}-\d{3}$/',
                'po_delivery_term'    => 'required|string|min:5|max:50',
                'po_payment_term'     => 'required|string|min:5|max:50',
                'items'               => 'required|array|min:1',
                'items.*.stock'       => 'nullable|integer|min:1|max:9999999',
                'items.*.unit'        => 'required|string|min:1|max:20',
                'items.*.description' => 'required|string|min:5|max:255',
                'items.*.quantity'    => 'required|integer|min:1|max:9999999',
                'items.*.cost'        => 'required|numeric|min:0.01|max:9999999',
                'items.*.specification' => 'nullable|string|min:5|max:1000',
            ];
        } else {
            $rules = [
                'po_supplier'         => 'nullable|string|min:5|max:100',
                'po_address'          => 'nullable|string|min:5|max:200',
                'po_tele'             => 'nullable|string|min:5|max:60',
                'po_tin'              => 'nullable|string|regex:/^\d{3}-\d{3}-\d{3}-\d{3}$/',
                'po_place_delivery'   => 'nullable|string|min:5|max:100',
                'po_date_delivery'    => 'nullable|date',
                'po_no'               => 'nullable|string|min:5|max:50',
                'po_date'             => 'nullable|date',
                'po_mode'             => 'nullable|string|min:5|max:50',
                'po_tuptin'           => 'nullable|string|regex:/^\d{3}-\d{3}-\d{3}-\d{3}$/',
                'po_delivery_term'    => 'nullable|string|min:5|max:50',
                'po_payment_term'     => 'nullable|string|min:5|max:50',
                'items'               => 'nullable|array',
                'items.*.stock'       => 'nullable|integer|min:1|max:9999999',
                'items.*.unit'        => 'nullable|string|min:1|max:20',
                'items.*.description' => 'nullable|string|min:5|max:255',
                'items.*.quantity'    => 'nullable|integer|min:1|max:9999999',
                'items.*.cost'        => 'nullable|numeric|min:0.01|max:9999999',
                'items.*.specification' => 'nullable|string|min:5|max:1000',
            ];
        }

        $messages = [
            'po_supplier.required'         => 'Supplier is required.',
            'po_supplier.min'              => 'Supplier must be at least 5 characters.',
            'po_supplier.max'              => 'Supplier must not exceed 100 characters.',
            'po_address.required'          => 'Address is required.',
            'po_address.min'               => 'Address must be at least 5 characters.',
            'po_address.max'               => 'Address must not exceed 200 characters.',
            'po_tele.required'             => 'Tel No. is required.',
            'po_tele.min'                  => 'Tel No. must be at least 5 characters.',
            'po_tele.max'                  => 'Tel No. must not exceed 60 characters.',
            'po_tin.required'              => 'TIN is required.',
            'po_tin.regex'                 => 'TIN must be formatted as XXX-XXX-XXX-XXX.',
            'po_place_delivery.required'   => 'Place of Delivery is required.',
            'po_place_delivery.min'        => 'Place of Delivery must be at least 5 characters.',
            'po_place_delivery.max'        => 'Place of Delivery must not exceed 100 characters.',
            'po_date_delivery.required'    => 'Date of Delivery is required.',
            'po_date_delivery.date'        => 'Date of Delivery must be a valid date.',
            'po_no.required'               => 'P.O. No. is required.',
            'po_no.min'                    => 'P.O. No. must be at least 5 characters.',
            'po_no.max'                    => 'P.O. No. must not exceed 50 characters.',
            'po_date.required'             => 'Date is required.',
            'po_date.date'                 => 'Date must be a valid date.',
            'po_mode.required'             => 'Mode of Procurement is required.',
            'po_mode.min'                  => 'Mode of Procurement must be at least 5 characters.',
            'po_mode.max'                  => 'Mode of Procurement must not exceed 50 characters.',
            'po_tuptin.required'           => 'TUP-Taguig TIN is required.',
            'po_tuptin.regex'              => 'TUP-Taguig TIN must be formatted as XXX-XXX-XXX-XXX.',
            'po_delivery_term.required'    => 'Delivery Term is required.',
            'po_delivery_term.min'         => 'Delivery Term must be at least 5 characters.',
            'po_delivery_term.max'         => 'Delivery Term must not exceed 50 characters.',
            'po_payment_term.required'     => 'Payment Term is required.',
            'po_payment_term.min'          => 'Payment Term must be at least 5 characters.',
            'po_payment_term.max'          => 'Payment Term must not exceed 50 characters.',
            'items.required'               => 'At least one item is required.',
            'items.min'                    => 'At least one item is required.',
            'items.*.stock.integer'        => 'Stock No. must be a whole number.',
            'items.*.stock.min'            => 'Stock No. must be at least 1.',
            'items.*.stock.max'            => 'Stock No. is too large.',
            'items.*.unit.required'        => 'Unit is required.',
            'items.*.unit.max'             => 'Unit must not exceed 20 characters.',
            'items.*.description.required' => 'Description is required.',
            'items.*.description.min'      => 'Description must be at least 5 characters.',
            'items.*.description.max'      => 'Description must not exceed 255 characters.',
            'items.*.quantity.required'    => 'Quantity is required.',
            'items.*.quantity.integer'     => 'Quantity must be a whole number.',
            'items.*.quantity.min'         => 'Quantity must be at least 1.',
            'items.*.quantity.max'         => 'Quantity is too large.',
            'items.*.cost.required'        => 'Unit cost is required.',
            'items.*.cost.numeric'         => 'Unit cost must be a number.',
            'items.*.cost.min'             => 'Unit cost must be at least 0.01.',
            'items.*.cost.max'             => 'Unit cost is too large.',
            'items.*.specification.min'    => 'Specification must be at least 5 characters.',
            'items.*.specification.max'    => 'Specification must not exceed 1000 characters.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    public function updatePo(Request $request, $po_id) {
        $po = PoParent::findOrFail($po_id);

        // Determine intent: 'Done' (strict) or 'Draft' (lenient)
        $intent = $request->input('po_status', 'Draft');

        // Validate based on intent
        $validator = $this->validatePo($request, $intent);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $po, $intent) {
                // Update PO Parent
                $po->update([
                    'po_supplier'       => $request->po_supplier,
                    'po_address'        => $request->po_address,
                    'po_tele'           => $request->po_tele,
                    'po_tin'            => $request->po_tin,
                    'po_place_delivery' => $request->po_place_delivery,
                    'po_date_delivery'  => $request->po_date_delivery,
                    'po_no'             => $request->po_no,
                    'po_date'           => $request->po_date,
                    'po_mode'           => $request->po_mode,
                    'po_tuptin'         => $request->po_tuptin,
                    'po_delivery_term'  => $request->po_delivery_term,
                    'po_payment_term'   => $request->po_payment_term,
                    'po_status'         => $intent,
                    'po_total_amount'   => $request->po_total_amount ?? 0,
                ]);

                // Sync Items: Delete and Recreate for simplicity (Beginner Friendly)
                // Delete specs first to avoid foreign key violation
                foreach ($po->poItems as $item) {
                    $item->poSpecs()->delete();
                }
                $po->poItems()->delete();

                if ($request->has('items')) {
                    foreach ($request->items as $itemData) {
                        $item = $po->poItems()->create([
                            'po_items_stockno'  => $itemData['stock'] ?? null,
                            'po_items_unit'     => $itemData['unit'] ?? null,
                            'po_items_descrip'  => $itemData['description'] ?? null,
                            'po_items_quantity' => $itemData['quantity'] ?? 0,
                            'po_items_cost'     => $itemData['cost'] ?? 0,
                            'po_items_total'    => ($itemData['quantity'] ?? 0) * ($itemData['cost'] ?? 0),
                        ]);

                        // Add Specification if present
                        if (!empty($itemData['specification'])) {
                            $item->poSpecs()->create([
                                'po_spec_description' => $itemData['specification'],
                            ]);
                        }
                    }
                }
            });

            $message = $intent === 'Done'
                ? 'Purchase Order submitted successfully.'
                : 'Purchase Order saved as draft.';

            session()->flash('success', $message);

            return response()->json([
                'success'  => true,
                'message'  => $message,
                'redirect' => route('show.create.po', ['po_id' => $po->po_id]),
            ]);

        } catch (\Exception $e) {
            Log::error('PO Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while saving. Please try again.',
            ], 500);
        }
    }

    private function convertNumberToWords($number) {
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

    public function exportPdf($po_id) {
        $po = PoParent::with(['poItems.poSpecs'])->findOrFail($po_id);

        if ($po->poItems->count() > 23) {
            return back()->with('error', 'Purchase Order exceeds maximum limit of 23 items for PDF export.');
        }

        $templatePath = base_path('procurement_documents/Purchase Order Template.xlsx');
        if (!file_exists($templatePath)) {
            return back()->with('error', 'Template file not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheet(0);

        // Map header fields
        $sheet->setCellValue('C4', $po->po_supplier ?? '');
        $sheet->setCellValue('F4', $po->po_no ?? '');
        $sheet->setCellValue('C5', $po->po_address ?? '');
        
        if ($po->po_date) {
            $sheet->setCellValue('F5', date('F j, Y', strtotime($po->po_date)));
        }
        
        $sheet->setCellValue('C6', $po->po_tele ?? '');
        $sheet->setCellValue('F6', $po->po_mode ?? '');
        $sheet->setCellValue('C7', $po->po_tin ?? '');
        $sheet->setCellValue('F7', $po->po_tuptin ?? '');
        $sheet->setCellValue('C10', $po->po_place_delivery ?? '');
        $sheet->setCellValue('F10', $po->po_delivery_term ?? '');
        
        if ($po->po_date_delivery) {
            $sheet->setCellValue('C11', date('F j, Y', strtotime($po->po_date_delivery)));
        }
        $sheet->setCellValue('F11', $po->po_payment_term ?? '');

        // Map item fields
        $currentRow = 13;
        foreach ($po->poItems as $item) {
            $sheet->setCellValue('A' . $currentRow, $item->po_items_stockno ?? '');
            $sheet->setCellValue('B' . $currentRow, $item->po_items_unit ?? '');
            
            $description = $item->po_items_descrip;
            $specs = $item->poSpecs->pluck('po_spec_description')->filter()->implode("\n");
            if ($specs) {
                $description .= "\n" . $specs;
            }
            $sheet->setCellValue('C' . $currentRow, $description ?? '');
            $sheet->setCellValue('D' . $currentRow, $item->po_items_quantity ?? 0);
            $sheet->setCellValue('E' . $currentRow, $item->po_items_cost ?? 0);
            $sheet->setCellValue('F' . $currentRow, $item->po_items_total ?? 0);
            
            $currentRow++;
        }

        // End of items marker
        $sheet->setCellValue('C' . $currentRow, '*** Nothing follows ***');
        $sheet->getStyle('C' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        // Summary fields (fixed rows as requested)
        $sheet->setCellValue('C38', $po->po_title ?? '');
        
        $amountInWords = $this->convertNumberToWords($po->po_total_amount) . ' PESOS ONLY';
        $sheet->setCellValue('C39', $amountInWords);
        $sheet->setCellValue('F39', $po->po_total_amount ?? 0);

        // Page setup for PDF (center horizontally and scale to fit width)
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);
        $sheet->getPageMargins()->setLeft(0.8);
        $sheet->getPageMargins()->setRight(0.8);
        $sheet->getPageMargins()->setTop(0.2);
        $sheet->getPageMargins()->setBottom(0.2);

        // Manually inject logo
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

        // Export to PDF
        $writer = new Mpdf($spreadsheet);
        $writer->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $writer->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $filename = 'Purchase_Order_' . ($po->po_no ?? $po->po_unique_code) . '.pdf';
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
