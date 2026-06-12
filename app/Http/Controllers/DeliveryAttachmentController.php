<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoParent;
use App\Models\Iar;
use App\Models\IarItem;
use App\Models\Ris;
use App\Models\RisItem;
use App\Models\Rsmi;
use App\Models\RsmiItem;
use App\Services\IarPdfExportService;
use App\Services\RisPdfExportService;
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
            'risSlips.department.users',
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
        if (
            !$po->iarReports()->exists() &&
            !$po->risSlips()->exists() &&
            !$po->icsSlips()->exists() &&
            !$po->parReceipts()->exists() &&
            !$po->rsmiReports()->exists() &&
            !$po->rspiReports()->exists() &&
            !$po->ndrReports()->exists()
        ) {
            return redirect()->route('show.po.review', ['po_id' => $po_id]);
        }

        $headPropertySupply = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('roles_tbl.role_id', 10);
        })->first();

        $users = \App\Models\User::all();

        $departments = \App\Models\Department::with('users')->get();

        return view('supply.pages.supply-delivery-attachment', compact('po', 'headPropertySupply', 'users', 'departments'));
    }

    public function exportIar($iar_id)
    {
        $iar = Iar::with(['iarItems.iarSpecs', 'purchaseOrder'])->findOrFail($iar_id);

        $pdfService = app(IarPdfExportService::class);
        return $pdfService->export($iar);
    }

    public function exportRis($ris_id)
    {
        $ris = Ris::with([
            'risItems.risSpecs',
            'purchaseOrder',
            'requester',
            'receiver',
            'issuer',
            'approver'
        ])->findOrFail($ris_id);

        $pdfService = app(RisPdfExportService::class);
        return $pdfService->export($ris);
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

    public function saveRis($ris_id, Request $request)
    {
        $ris = Ris::findOrFail($ris_id);

        $validated = $request->validate([
            'ris_fund_cluster' => 'nullable|string|max:100',
            'ris_no' => 'nullable|string|max:50',
            'ris_center_code' => 'nullable|string|max:50',
            'ris_received_by' => 'nullable|integer|exists:users,user_id',
            'ris_received_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.ris_items_id' => 'nullable|integer',
            'items.*.ris_stock_no' => 'nullable|string|max:50',
            'items.*.ris_unit' => 'nullable|string|max:20',
            'items.*.ris_items_descrip' => 'nullable|string|max:255',
            'items.*.ris_quantity' => 'nullable|integer',
            'items.*.ris_stock_available' => 'nullable|in:Yes,No',
            'items.*.ris_issued_quantity' => 'nullable|integer',
            'items.*.ris_issued_remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $ris->update([
                'ris_fund_cluster' => $validated['ris_fund_cluster'] ?? null,
                'ris_no' => $validated['ris_no'] ?? null,
                'ris_center_code' => $validated['ris_center_code'] ?? null,
                'ris_received_by' => $validated['ris_received_by'] ?? null,
                'ris_received_date' => $validated['ris_received_date'] ?? null,
            ]);

            $incomingItemIds = [];

            foreach ($validated['items'] as $itemData) {
                if (!empty($itemData['ris_items_id'])) {
                    // Update existing item
                    $risItem = RisItem::where('ris_id_fk', $ris->ris_id)
                        ->findOrFail($itemData['ris_items_id']);

                    $risItem->update([
                        'ris_stock_no' => $itemData['ris_stock_no'] ?? null,
                        'ris_unit' => $itemData['ris_unit'] ?? null,
                        'ris_items_descrip' => $itemData['ris_items_descrip'] ?? null,
                        'ris_quantity' => $itemData['ris_quantity'] ?? null,
                        'ris_stock_available' => $itemData['ris_stock_available'] ?? null,
                        'ris_issued_quantity' => $itemData['ris_issued_quantity'] ?? null,
                        'ris_issued_remarks' => $itemData['ris_issued_remarks'] ?? null,
                    ]);

                    $incomingItemIds[] = $risItem->ris_items_id;
                } else {
                    // Create new item
                    $risItem = RisItem::create([
                        'ris_id_fk' => $ris->ris_id,
                        'ris_stock_no' => $itemData['ris_stock_no'] ?? null,
                        'ris_unit' => $itemData['ris_unit'] ?? null,
                        'ris_items_descrip' => $itemData['ris_items_descrip'] ?? null,
                        'ris_quantity' => $itemData['ris_quantity'] ?? null,
                        'ris_stock_available' => $itemData['ris_stock_available'] ?? null,
                        'ris_issued_quantity' => $itemData['ris_issued_quantity'] ?? null,
                        'ris_issued_remarks' => $itemData['ris_issued_remarks'] ?? null,
                    ]);

                    $incomingItemIds[] = $risItem->ris_items_id;
                }

                // Delete specs associated with this item to prevent duplicates
                $risItem->risSpecs()->delete();
            }

            // Delete items that were removed in the UI (not present in incoming request)
            RisItem::where('ris_id_fk', $ris->ris_id)
                ->whereNotIn('ris_items_id', $incomingItemIds)
                ->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Requisition and Issue Slip saved successfully.')
                ->with('active_document', 'doc-ris-' . $ris->ris_id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to save Requisition and Issue Slip: ' . $e->getMessage())
                ->with('active_document', 'doc-ris-' . $ris->ris_id);
        }
    }

    public function saveRsmi($rsmi_id, Request $request)
    {
        $rsmi = Rsmi::findOrFail($rsmi_id);

        $validated = $request->validate([
            'fund_cluster' => 'nullable|string|max:100',
            'serial_no' => 'nullable|string|max:50',
            'date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.rsmi_items_id' => 'nullable|integer',
            'items.*.ris_no' => 'nullable|string|max:50',
            'items.*.responsibility_center_code' => 'nullable|string|max:50',
            'items.*.stock_no' => 'nullable|string|max:50',
            'items.*.item_description' => 'nullable|string|max:255',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.qty_issued' => 'nullable|integer',
            'items.*.unit_cost' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $rsmiTotal = 0;
            $incomingItemIds = [];

            foreach ($validated['items'] as $itemData) {
                $qty = isset($itemData['qty_issued']) ? intval($itemData['qty_issued']) : 0;
                $unitCost = isset($itemData['unit_cost']) ? floatval($itemData['unit_cost']) : 0;
                $amount = $qty * $unitCost;
                $rsmiTotal += $amount;

                if (!empty($itemData['rsmi_items_id'])) {
                    // Update existing item
                    $rsmiItem = RsmiItem::where('rsmi_id_fk', $rsmi->rsmi_id)
                        ->findOrFail($itemData['rsmi_items_id']);

                    $rsmiItem->update([
                        'rsmi_ris_no' => $itemData['ris_no'] ?? null,
                        'rsmi_center_code' => $itemData['responsibility_center_code'] ?? null,
                        'rsmi_stock_no' => $itemData['stock_no'] ?? null,
                        'rsmi_items_descrip' => $itemData['item_description'] ?? null,
                        'rsmi_unit' => $itemData['unit'] ?? null,
                        'rsmi_quantity' => $qty,
                        'rsmi_unit_cost' => $unitCost,
                        'rsmi_amount' => $amount,
                    ]);

                    $incomingItemIds[] = $rsmiItem->rsmi_items_id;
                } else {
                    // Create new item
                    $rsmiItem = RsmiItem::create([
                        'rsmi_id_fk' => $rsmi->rsmi_id,
                        'rsmi_ris_no' => $itemData['ris_no'] ?? null,
                        'rsmi_center_code' => $itemData['responsibility_center_code'] ?? null,
                        'rsmi_stock_no' => $itemData['stock_no'] ?? null,
                        'rsmi_items_descrip' => $itemData['item_description'] ?? null,
                        'rsmi_unit' => $itemData['unit'] ?? null,
                        'rsmi_quantity' => $qty,
                        'rsmi_unit_cost' => $unitCost,
                        'rsmi_amount' => $amount,
                    ]);

                    $incomingItemIds[] = $rsmiItem->rsmi_items_id;
                }

                $rsmiItem->rsmiSpecs()->delete();
            }

            // Update RSMI Header
            $rsmi->update([
                'rsmi_fund_cluster' => $validated['fund_cluster'] ?? null,
                'rsmi_serial_no' => $validated['serial_no'] ?? null,
                'rsmi_date' => $validated['date'] ?? null,
                'rsmi_total' => $rsmiTotal,
            ]);

            // Remove deleted items
            RsmiItem::where('rsmi_id_fk', $rsmi->rsmi_id)
                ->whereNotIn('rsmi_items_id', $incomingItemIds)
                ->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Report of Supplies and Materials Issued saved successfully.')
                ->with('active_document', 'doc-rsmi-' . $rsmi->rsmi_id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to save Report of Supplies and Materials Issued: ' . $e->getMessage())
                ->with('active_document', 'doc-rsmi-' . $rsmi->rsmi_id);
        }
    }
}
