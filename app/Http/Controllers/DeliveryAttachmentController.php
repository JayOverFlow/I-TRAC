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
use App\Models\Ics;
use App\Models\IcsItem;
use App\Services\IarPdfExportService;
use App\Services\RisPdfExportService;
use App\Services\RsmiPdfExportService;
use Illuminate\Support\Facades\DB;

class DeliveryAttachmentController extends Controller
{
    public function showDeliveryAttachment($po_id)
    {
        $po = PoParent::with([
            'iarReports.iarItems.iarSpecs',
            'risSlips.risItems.risSpecs',
            'risSlips.risItems.poItem',
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

    public function exportRsmi($rsmi_id)
    {
        $rsmi = Rsmi::with([
            'rsmiItems.rsmiSpecs',
            'purchaseOrder',
            'user'
        ])->findOrFail($rsmi_id);

        $pdfService = app(RsmiPdfExportService::class);
        return $pdfService->export($rsmi);
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

        $firstItem = $ris->risItems->first();
        $isSemiExpendable = $firstItem && $firstItem->poItem && $firstItem->poItem->po_items_category === 'Semi-Expendable';

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
            $receivedBy = $validated['ris_received_by'] ?? null;
            if ($isSemiExpendable) {
                $receivedBy = $ris->ris_received_by;
            }

            $ris->update([
                'ris_fund_cluster' => $validated['ris_fund_cluster'] ?? null,
                'ris_no' => $validated['ris_no'] ?? null,
                'ris_center_code' => $validated['ris_center_code'] ?? null,
                'ris_received_by' => $receivedBy,
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
            'rsmi_fund_cluster' => 'nullable|string|max:100',
            'rsmi_serial_no' => 'nullable|string|max:50',
            'rsmi_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.rsmi_items_id' => 'nullable|integer',
            'items.*.rsmi_ris_no' => 'nullable|string|max:50',
            'items.*.rsmi_center_code' => 'nullable|string|max:50',
            'items.*.rsmi_stock_no' => 'nullable|string|max:50',
            'items.*.rsmi_items_descrip' => 'nullable|string|max:255',
            'items.*.rsmi_unit' => 'nullable|string|max:20',
            'items.*.rsmi_quantity' => 'nullable|integer',
            'items.*.rsmi_unit_cost' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $rsmiTotal = 0;
            $incomingItemIds = [];

            foreach ($validated['items'] as $itemData) {
                $qty = isset($itemData['rsmi_quantity']) ? intval($itemData['rsmi_quantity']) : 0;
                $unitCost = isset($itemData['rsmi_unit_cost']) ? floatval($itemData['rsmi_unit_cost']) : 0;
                $amount = $qty * $unitCost;
                $rsmiTotal += $amount;

                if (!empty($itemData['rsmi_items_id'])) {
                    // Update existing item
                    $rsmiItem = RsmiItem::where('rsmi_id_fk', $rsmi->rsmi_id)
                        ->findOrFail($itemData['rsmi_items_id']);

                    $rsmiItem->update([
                        'rsmi_ris_no' => $itemData['rsmi_ris_no'] ?? null,
                        'rsmi_center_code' => $itemData['rsmi_center_code'] ?? null,
                        'rsmi_stock_no' => $itemData['rsmi_stock_no'] ?? null,
                        'rsmi_items_descrip' => $itemData['rsmi_items_descrip'] ?? null,
                        'rsmi_unit' => $itemData['rsmi_unit'] ?? null,
                        'rsmi_quantity' => $qty,
                        'rsmi_unit_cost' => $unitCost,
                        'rsmi_amount' => $amount,
                    ]);

                    $incomingItemIds[] = $rsmiItem->rsmi_items_id;
                } else {
                    // Create new item
                    $rsmiItem = RsmiItem::create([
                        'rsmi_id_fk' => $rsmi->rsmi_id,
                        'rsmi_ris_no' => $itemData['rsmi_ris_no'] ?? null,
                        'rsmi_center_code' => $itemData['rsmi_center_code'] ?? null,
                        'rsmi_stock_no' => $itemData['rsmi_stock_no'] ?? null,
                        'rsmi_items_descrip' => $itemData['rsmi_items_descrip'] ?? null,
                        'rsmi_unit' => $itemData['rsmi_unit'] ?? null,
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
                'rsmi_fund_cluster' => $validated['rsmi_fund_cluster'] ?? null,
                'rsmi_serial_no' => $validated['rsmi_serial_no'] ?? null,
                'rsmi_date' => $validated['rsmi_date'] ?? null,
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

    public function saveIcs($ics_id, Request $request)
    {
        $ics = Ics::findOrFail($ics_id);

        $validated = $request->validate([
            'ics_fund_cluster' => 'nullable|string|max:100',
            'ics_no' => 'nullable|string|max:50',
            'ics_code_no' => 'nullable|string|max:50',
            'ics_received_from_date' => 'nullable|date',
            'ics_received_by_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.ics_items_id' => 'nullable|integer',
            'items.*.ics_quantity' => 'nullable|integer',
            'items.*.ics_unit' => 'nullable|string|max:20',
            'items.*.ics_unit_cost' => 'nullable|numeric',
            'items.*.ics_items_descrip' => 'nullable|string|max:255',
            'items.*.ics_inventory_item_no' => 'nullable|string|max:50',
            'items.*.ics_estimated_useful_life' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $ics->update([
                'ics_fund_cluster' => $validated['ics_fund_cluster'] ?? null,
                'ics_no' => $validated['ics_no'] ?? null,
                'ics_code_no' => $validated['ics_code_no'] ?? null,
                'ics_received_from_date' => $validated['ics_received_from_date'] ?? null,
                'ics_received_by_date' => $validated['ics_received_by_date'] ?? null,
            ]);

            $incomingItemIds = [];

            foreach ($validated['items'] as $itemData) {
                $qty = isset($itemData['ics_quantity']) ? intval($itemData['ics_quantity']) : 0;
                $unitCost = isset($itemData['ics_unit_cost']) ? floatval($itemData['ics_unit_cost']) : 0;
                $totalCost = $qty * $unitCost;

                if (!empty($itemData['ics_items_id'])) {
                    // Update existing item
                    $icsItem = IcsItem::where('ics_id_fk', $ics->ics_id)
                        ->findOrFail($itemData['ics_items_id']);

                    $icsItem->update([
                        'ics_quantity' => $qty,
                        'ics_unit' => $itemData['ics_unit'] ?? null,
                        'ics_unit_cost' => $unitCost,
                        'ics_total_cost' => $totalCost,
                        'ics_items_descrip' => $itemData['ics_items_descrip'] ?? null,
                        'ics_inventory_item_no' => $itemData['ics_inventory_item_no'] ?? null,
                        'ics_estimated_useful_life' => $itemData['ics_estimated_useful_life'] ?? null,
                    ]);

                    $incomingItemIds[] = $icsItem->ics_items_id;
                } else {
                    // Create new item
                    $icsItem = IcsItem::create([
                        'ics_id_fk' => $ics->ics_id,
                        'ics_quantity' => $qty,
                        'ics_unit' => $itemData['ics_unit'] ?? null,
                        'ics_unit_cost' => $unitCost,
                        'ics_total_cost' => $totalCost,
                        'ics_items_descrip' => $itemData['ics_items_descrip'] ?? null,
                        'ics_inventory_item_no' => $itemData['ics_inventory_item_no'] ?? null,
                        'ics_estimated_useful_life' => $itemData['ics_estimated_useful_life'] ?? null,
                    ]);

                    $incomingItemIds[] = $icsItem->ics_items_id;
                }

                // Since description and specifications are consolidated, clear/delete specs to prevent duplication
                $icsItem->icsSpecs()->delete();
            }

            // Delete items that were removed in the UI (not present in incoming request)
            IcsItem::where('ics_id_fk', $ics->ics_id)
                ->whereNotIn('ics_items_id', $incomingItemIds)
                ->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Inventory Custodian Slip saved successfully.')
                ->with('active_document', 'doc-ics-' . $ics->ics_id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to save Inventory Custodian Slip: ' . $e->getMessage())
                ->with('active_document', 'doc-ics-' . $ics->ics_id);
        }
    }
}
