<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoParent;
use App\Models\Iar;
use App\Models\IarItem;
use App\Services\IarPdfExportService;
use Illuminate\Support\Facades\DB;

class DeliveryAttachmentController extends Controller
{
    public function showDeliveryAttachment($po_id)
    {
        $po = PoParent::with([
            'iarReports.iarItems.iarSpecs',
            'risSlips.risItems.risSpecs',
            'risSlips.requester',
            'risSlips.receiver',
            'rsmiReports.rsmiItems.rsmiSpecs',
            'rsmiReports.user',
            'icsSlips.icsItems.icsSpecs',
            'icsSlips.receiver.departments',
            'icsSlips.giver',
            'rspiReports.rspiItems.rspiSpecs',
            'rspiReports.user',
            'parReceipts.parItems.parSpecs',
            'parReceipts.receiver.departments',
            'parReceipts.issuer',
            'ndrReports.ndrItems.ndrSpecs',
            'ndrReports.reporter'
        ])->findOrFail($po_id);

        // Redirect to PO Review if no delivery attachments have been generated
        if (!$po->iarReports()->exists() && 
            !$po->risSlips()->exists() && 
            !$po->icsSlips()->exists() && 
            !$po->parReceipts()->exists() && 
            !$po->rsmiReports()->exists() && 
            !$po->rspiReports()->exists() &&
            !$po->ndrReports()->exists()) {
            return redirect()->route('show.po.review', ['po_id' => $po_id]);
        }

        $headPropertySupply = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('roles_tbl.role_id', 10);
        })->first();

        $users = \App\Models\User::all();

        return view('supply.pages.supply-delivery-attachment', compact('po', 'headPropertySupply', 'users'));
    }

    public function exportIar($iar_id)
    {
        $iar = Iar::with(['iarItems.iarSpecs', 'purchaseOrder'])->findOrFail($iar_id);

        $pdfService = app(IarPdfExportService::class);
        return $pdfService->export($iar);
    }

    public function saveIar($iar_id, Request $request)
    {
        $iar = Iar::findOrFail($iar_id);

        $validated = $request->validate([
            'responsibility_center_code' => 'nullable|string|max:50',
            'fund_cluster' => 'nullable|string|max:100',
            'iar_no' => 'nullable|string|max:50',
            'iar_date' => 'nullable|date',
            'invoice_no' => 'nullable|string|max:50',
            'invoice_date' => 'nullable|date',
            'inspection_officer' => 'nullable|string|max:255',
            'date_received' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.iar_items_id' => 'nullable|integer',
            'items.*.stock_no' => 'nullable|string|max:50',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.quantity' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            // Update the IAR header fields
            $iar->update([
                'iar_center_code' => $validated['responsibility_center_code'] ?? null,
                'iar_fund_cluster' => $validated['fund_cluster'] ?? null,
                'iar_no' => $validated['iar_no'] ?? null,
                'iar_date' => $validated['iar_date'] ?? null,
                'iar_invoice_no' => $validated['invoice_no'] ?? null,
                'iar_invoice_date' => $validated['invoice_date'] ?? null,
                'iar_inspected_by' => $validated['inspection_officer'] ?? null,
                'iar_date_accepted' => $validated['date_received'] ?? null,
            ]);

            // Track incoming item IDs to identify which ones to delete
            $incomingItemIds = [];

            foreach ($validated['items'] as $itemData) {
                if (!empty($itemData['iar_items_id'])) {
                    // Update existing item
                    $iarItem = IarItem::where('iar_id_fk', $iar->iar_id)
                        ->findOrFail($itemData['iar_items_id']);
                    
                    $iarItem->update([
                        'iar_stock_no' => $itemData['stock_no'] ?? null,
                        'iar_items_descrip' => $itemData['description'] ?? null,
                        'iar_unit' => $itemData['unit'] ?? null,
                        'iar_quantity' => $itemData['quantity'] ?? null,
                    ]);
                    
                    $incomingItemIds[] = $iarItem->iar_items_id;
                } else {
                    // Create new item
                    $iarItem = IarItem::create([
                        'iar_id_fk' => $iar->iar_id,
                        'iar_stock_no' => $itemData['stock_no'] ?? null,
                        'iar_items_descrip' => $itemData['description'] ?? null,
                        'iar_unit' => $itemData['unit'] ?? null,
                        'iar_quantity' => $itemData['quantity'] ?? null,
                    ]);
                    
                    $incomingItemIds[] = $iarItem->iar_items_id;
                }

                // Since description and specifications are consolidated into iar_items_descrip,
                // we clear/delete any specs associated with this item to prevent duplicates
                $iarItem->iarSpecs()->delete();
            }

            // Delete items that were removed in the UI (not present in incoming request)
            IarItem::where('iar_id_fk', $iar->iar_id)
                ->whereNotIn('iar_items_id', $incomingItemIds)
                ->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Inspection and Acceptance Report saved successfully.')
                ->with('active_document', 'doc-iar-' . $iar->iar_id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to save Inspection and Acceptance Report: ' . $e->getMessage())
                ->with('active_document', 'doc-iar-' . $iar->iar_id);
        }
    }
}
