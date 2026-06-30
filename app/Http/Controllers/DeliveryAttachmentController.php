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
use App\Models\Rspi;
use App\Models\RspiItem;
use App\Models\Par;
use App\Models\ParItem;
use App\Models\Mr;
use App\Services\IarPdfExportService;
use App\Services\RisPdfExportService;
use App\Services\RsmiPdfExportService;
use App\Services\IcsPdfExportService;
use App\Services\RspiPdfExportService;
use App\Services\ParPdfExportService;
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

        $iar->is_exported = 1;
        $iar->save();
        if ($iar->purchaseOrder) {
            $iar->purchaseOrder->checkAndSetDaExportStatus();
        }

        $pdfService = app(IarPdfExportService::class);
        return $pdfService->export($iar);
    }

    public function exportRis($ris_id)
    {
        $ris = Ris::with([
            'risItems.risSpecs',
            'risItems.poItem',
            'purchaseOrder',
            'requester',
            'receiver',
            'issuer',
            'approver'
        ])->findOrFail($ris_id);

        $firstItem = $ris->risItems->first();
        $isSemiExpendable = $firstItem && $firstItem->poItem && $firstItem->poItem->po_items_category === 'Semi-Expendable';

        if ($isSemiExpendable) {
            DB::transaction(function () use ($ris) {
                foreach ($ris->risItems as $item) {
                    if ($item->poItem && $item->poItem->po_items_category === 'Semi-Expendable') {
                        if ($item->ris_po_items_id_fk) {
                            // Avoid duplicates using Eloquent relationship
                            if (!$item->poItem->mrs()->exists()) {
                                // Generate unique numeric code in the format MR-XXXX-XXXX
                                do {
                                    $qrCode = 'MR-' . mt_rand(1000, 9999) . '-' . mt_rand(1000, 9999);
                                } while (Mr::where('mr_qr_code', $qrCode)->exists());

                                $item->poItem->mrs()->create([
                                    'mr_qr_code'    => $qrCode,
                                    'item_name'     => $item->ris_items_descrip,
                                    'specification' => $item->risSpecs->pluck('ris_spec_description')->filter()->implode("\n"),
                                    'quantity'      => $item->ris_issued_quantity,
                                    'unit'          => $item->ris_unit,
                                    'stock'         => $item->ris_stock_no,
                                    'is_assigned'   => 0,
                                    'assigned_to'   => null,
                                    'category'      => 'Semi-Expendable',
                                ]);
                            }
                        }
                    }
                }
            });

            // Reload ris details to ensure latest values
            $ris->load('risItems.risSpecs', 'risItems.poItem');
        }

        $ris->is_exported = 1;
        $ris->save();
        if ($ris->purchaseOrder) {
            $ris->purchaseOrder->checkAndSetDaExportStatus();
        }

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

        $rsmi->is_exported = 1;
        $rsmi->save();
        if ($rsmi->purchaseOrder) {
            $rsmi->purchaseOrder->checkAndSetDaExportStatus();
        }

        $pdfService = app(RsmiPdfExportService::class);
        return $pdfService->export($rsmi);
    }

    public function exportIcs($ics_id)
    {
        $ics = Ics::with([
            'icsItems.icsSpecs',
            'purchaseOrder',
            'receiver.departments',
            'giver'
        ])->findOrFail($ics_id);

        $ics->is_exported = 1;
        $ics->save();
        if ($ics->purchaseOrder) {
            $ics->purchaseOrder->checkAndSetDaExportStatus();
        }

        $pdfService = app(IcsPdfExportService::class);
        return $pdfService->export($ics);
    }

    public function exportRspi($rspi_id)
    {
        $rspi = Rspi::with([
            'rspiItems.rspiSpecs',
            'purchaseOrder',
            'user.roles'
        ])->findOrFail($rspi_id);

        $rspi->is_exported = 1;
        $rspi->save();
        if ($rspi->purchaseOrder) {
            $rspi->purchaseOrder->checkAndSetDaExportStatus();
        }

        $pdfService = app(RspiPdfExportService::class);
        return $pdfService->export($rspi);
    }

    public function exportPar($par_id)
    {
        $par = Par::with([
            'parItems.parSpecs',
            'parItems.poItem',
            'purchaseOrder',
            'receiver.departments',
            'issuer.roles'
        ])->findOrFail($par_id);

        DB::transaction(function () use ($par) {
            foreach ($par->parItems as $item) {
                if ($item->par_po_items_id_fk) {
                    if ($item->poItem) {
                        // Avoid duplicates using Eloquent relationship
                        if (!$item->poItem->mrs()->exists()) {
                            // Generate unique numeric code in the format MR-XXXX-XXXX
                            do {
                                $qrCode = 'MR-' . mt_rand(1000, 9999) . '-' . mt_rand(1000, 9999);
                            } while (Mr::where('mr_qr_code', $qrCode)->exists());

                            $item->poItem->mrs()->create([
                                'mr_qr_code'    => $qrCode,
                                'item_name'     => $item->par_items_descrip,
                                'specification' => $item->parSpecs->pluck('par_spec_description')->filter()->implode("\n"),
                                'quantity'      => $item->par_quantity,
                                'unit'          => $item->par_unit,
                                'stock'         => $item->par_property_no,
                                'is_assigned'   => 0,
                                'assigned_to'   => null,
                                'category'      => 'Equipment',
                            ]);
                        }
                    }
                }
            }
        });

        // Reload par details to ensure latest values
        $par->load('parItems.parSpecs', 'parItems.poItem');

        $par->is_exported = 1;
        $par->save();
        if ($par->purchaseOrder) {
            $par->purchaseOrder->checkAndSetDaExportStatus();
        }

        $pdfService = app(ParPdfExportService::class);
        return $pdfService->export($par);
    }

    public function saveIar($iar_id, Request $request)
    {
        $iar = Iar::findOrFail($iar_id);

        $intent = $request->input('export_pdf') === '1' ? 'Done' : 'Draft';

        if ($intent === 'Done') {
            $rules = [
                'iar_center_code' => 'required|string|min:1|max:50',
                'iar_fund_cluster' => 'required|string|min:1|max:50',
                'iar_no' => 'required|string|min:1|max:50',
                'iar_date' => 'required|date',
                'iar_invoice_no' => 'required|string|min:1|max:50',
                'iar_invoice_date' => 'required|date',
                'iar_inspected_by' => 'required|string|min:5|max:50',
                'iar_date_accepted' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.iar_items_id' => 'nullable|integer',
                'items.*.iar_stock_no' => 'nullable|string|min:1|max:20',
                'items.*.iar_items_descrip' => 'required|string|min:5|max:50',
                'items.*.iar_unit' => 'required|string|min:2|max:20',
                'items.*.iar_quantity' => 'required|integer|min:1|max:9999999',
                'items.*.specification' => 'required|string|min:5|max:500',
            ];
        } else {
            $rules = [
                'iar_center_code' => 'nullable|string|min:1|max:50',
                'iar_fund_cluster' => 'nullable|string|min:1|max:50',
                'iar_no' => 'nullable|string|min:2|max:50',
                'iar_date' => 'nullable|date',
                'iar_invoice_no' => 'nullable|string|min:2|max:50',
                'iar_invoice_date' => 'nullable|date',
                'iar_inspected_by' => 'nullable|string|min:5|max:50',
                'iar_date_accepted' => 'nullable|date',
                'items' => 'nullable|array',
                'items.*.iar_items_id' => 'nullable|integer',
                'items.*.iar_stock_no' => 'nullable|string|min:1|max:20',
                'items.*.iar_items_descrip' => 'nullable|string|min:5|max:50',
                'items.*.iar_unit' => 'nullable|string|min:2|max:20',
                'items.*.iar_quantity' => 'nullable|integer|min:1|max:9999999',
                'items.*.specification' => 'nullable|string|max:500',
            ];
        }

        $messages = [
            'iar_center_code.required' => 'Responsibility Center Code is required.',
            'iar_center_code.max' => 'Responsibility Center Code must not exceed 50 characters.',
            'iar_fund_cluster.required' => 'Fund Cluster is required.',
            'iar_fund_cluster.max' => 'Fund Cluster must not exceed 50 characters.',
            'iar_no.required' => 'IAR Number is required.',
            'iar_no.min' => 'IAR Number must be at least 2 characters.',
            'iar_no.max' => 'IAR Number must not exceed 50 characters.',
            'iar_date.required' => 'IAR Date is required.',
            'iar_date.date' => 'Must be a valid date.',
            'iar_invoice_no.required' => 'Invoice Number is required.',
            'iar_invoice_no.min' => 'Invoice Number must be at least 2 characters.',
            'iar_invoice_no.max' => 'Invoice Number must not exceed 50 characters.',
            'iar_invoice_date.required' => 'Invoice Date is required.',
            'iar_invoice_date.date' => 'Must be a valid date.',
            'iar_inspected_by.required' => 'Inspection Officer Name is required.',
            'iar_inspected_by.min' => 'Officer Name must be at least 5 characters.',
            'iar_inspected_by.max' => 'Officer Name must not exceed 50 characters.',
            'iar_date_accepted.required' => 'Date Received is required.',
            'iar_date_accepted.date' => 'Must be a valid date.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.iar_stock_no.min' => 'Stock Number must be at least 1 character.',
            'items.*.iar_stock_no.max' => 'Stock Number must not exceed 20 characters.',
            'items.*.iar_items_descrip.required' => 'Description is required.',
            'items.*.iar_items_descrip.min' => 'Description must be at least 5 characters.',
            'items.*.iar_items_descrip.max' => 'Description must not exceed 50 characters.',
            'items.*.iar_unit.required' => 'Unit is required.',
            'items.*.iar_unit.min' => 'Unit must be at least 2 characters.',
            'items.*.iar_unit.max' => 'Unit must not exceed 20 characters.',
            'items.*.iar_quantity.required' => 'Quantity is required.',
            'items.*.iar_quantity.integer' => 'Quantity must be an integer.',
            'items.*.iar_quantity.min' => 'Quantity must be at least 1.',
            'items.*.iar_quantity.max' => 'Quantity exceeds maximum limit.',
            'items.*.specification.required' => 'Specification is required.',
            'items.*.specification.min' => 'Specification must be at least 5 characters.',
            'items.*.specification.max' => 'Specification must not exceed 500 characters.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_document', 'doc-iar-' . $iar->iar_id);
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            // Update the IAR header fields
            $iar->update([
                'iar_center_code' => $validated['iar_center_code'] ?? null,
                'iar_fund_cluster' => $validated['iar_fund_cluster'] ?? null,
                'iar_no' => $validated['iar_no'] ?? null,
                'iar_date' => $validated['iar_date'] ?? null,
                'iar_invoice_no' => $validated['iar_invoice_no'] ?? null,
                'iar_invoice_date' => $validated['iar_invoice_date'] ?? null,
                'iar_inspected_by' => $validated['iar_inspected_by'] ?? null,
                'iar_date_accepted' => $validated['iar_date_accepted'] ?? null,
            ]);

            // Track incoming item IDs to identify which ones to delete
            $incomingItemIds = [];

            if (isset($validated['items']) && is_array($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    if (!empty($itemData['iar_items_id'])) {
                        // Update existing item
                        $iarItem = IarItem::where('iar_id_fk', $iar->iar_id)
                            ->findOrFail($itemData['iar_items_id']);

                        $iarItem->update([
                            'iar_stock_no' => $itemData['iar_stock_no'] ?? null,
                            'iar_items_descrip' => $itemData['iar_items_descrip'] ?? null,
                            'iar_unit' => $itemData['iar_unit'] ?? null,
                            'iar_quantity' => $itemData['iar_quantity'] ?? null,
                        ]);

                        $incomingItemIds[] = $iarItem->iar_items_id;
                    } else {
                        // Create new item
                        $iarItem = IarItem::create([
                            'iar_id_fk' => $iar->iar_id,
                            'iar_stock_no' => $itemData['iar_stock_no'] ?? null,
                            'iar_items_descrip' => $itemData['iar_items_descrip'] ?? null,
                            'iar_unit' => $itemData['iar_unit'] ?? null,
                            'iar_quantity' => $itemData['iar_quantity'] ?? null,
                        ]);

                        $incomingItemIds[] = $iarItem->iar_items_id;
                    }

                    $spec = $iarItem->iarSpecs()->first();
                    if (!empty($itemData['specification'])) {
                        if ($spec) {
                            $spec->update(['iar_spec_description' => $itemData['specification']]);
                        } else {
                            $iarItem->iarSpecs()->create(['iar_spec_description' => $itemData['specification']]);
                        }
                    } else {
                        if ($spec) {
                            $spec->delete();
                        }
                    }
                }
            }

            // Delete items that were removed in the UI (not present in incoming request)
            IarItem::where('iar_id_fk', $iar->iar_id)
                ->whereNotIn('iar_items_id', $incomingItemIds)
                ->delete();

            DB::commit();

            $message = $intent === 'Done'
                ? 'Inspection and Acceptance Report saved and exported successfully.'
                : 'Inspection and Acceptance Report saved as draft.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'active_document' => 'doc-iar-' . $iar->iar_id,
                    'download_pdf' => $request->input('export_pdf') === '1' ? route('export.iar.pdf', $iar->iar_id) : null
                ]);
            }

            $response = redirect()->back()
                ->with('success', $message)
                ->with('active_document', 'doc-iar-' . $iar->iar_id);

            if ($request->input('export_pdf') === '1') {
                $response->with('download_pdf', route('export.iar.pdf', $iar->iar_id));
            }

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save Inspection and Acceptance Report: ' . $e->getMessage()
                ], 500);
            }

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

        $intent = $request->input('export_pdf') === '1' ? 'Done' : 'Draft';

        if ($intent === 'Done') {
            $rules = [
                'ris_fund_cluster' => 'required|string|min:1|max:50',
                'ris_no' => 'required|string|min:2|max:50',
                'ris_center_code' => 'required|string|min:1|max:50',
                'ris_received_date' => 'required|date',
                'ris_received_by' => 'required|integer|exists:users,user_id',
                'items' => 'required|array|min:1',
                'items.*.ris_items_id' => 'nullable|integer',
                'items.*.ris_stock_no' => 'nullable|string|min:1|max:20',
                'items.*.ris_unit' => 'required|string|min:2|max:20',
                'items.*.ris_items_descrip' => 'required|string|min:2|max:50',
                'items.*.ris_quantity' => 'nullable|integer|min:1|max:9999999',
                'items.*.ris_stock_available' => 'nullable|in:Yes,No',
                'items.*.ris_issued_quantity' => 'required|integer|min:1|max:9999999',
                'items.*.ris_issued_remarks' => 'nullable|string|max:50',
                'items.*.specification' => 'required|string|min:5|max:500',
            ];
        } else {
            $rules = [
                'ris_fund_cluster' => 'nullable|string|max:50',
                'ris_no' => 'nullable|string|max:50',
                'ris_center_code' => 'nullable|string|max:50',
                'ris_received_date' => 'nullable|date',
                'ris_received_by' => 'nullable|integer|exists:users,user_id',
                'items' => 'nullable|array',
                'items.*.ris_items_id' => 'nullable|integer',
                'items.*.ris_stock_no' => 'nullable|string|max:20',
                'items.*.ris_unit' => 'nullable|string|max:20',
                'items.*.ris_items_descrip' => 'nullable|string|max:255',
                'items.*.ris_quantity' => 'nullable|integer|min:1|max:9999999',
                'items.*.ris_stock_available' => 'nullable|in:Yes,No',
                'items.*.ris_issued_quantity' => 'nullable|integer|min:1|max:9999999',
                'items.*.ris_issued_remarks' => 'nullable|string|max:255',
                'items.*.specification' => 'nullable|string|max:500',
            ];
        }

        $messages = [
            'ris_fund_cluster.required' => 'Fund Cluster is required.',
            'ris_fund_cluster.max' => 'Must not exceed 50 characters.',
            'ris_no.required' => 'RIS Number is required.',
            'ris_no.min' => 'Must be at least 2 characters.',
            'ris_no.max' => 'Must not exceed 50 characters.',
            'ris_center_code.required' => 'Responsibility Center Code is required.',
            'ris_center_code.max' => 'Must not exceed 50 characters.',
            'ris_received_date.required' => 'Date is required.',
            'ris_received_date.date' => 'Must be a valid date.',
            'ris_received_by.required' => 'Received by is required.',
            'ris_received_by.exists' => 'Selected user is invalid.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.ris_stock_no.max' => 'Must not exceed 20 characters.',
            'items.*.ris_unit.required' => 'Unit is required.',
            'items.*.ris_unit.min' => 'Must be at least 2 characters.',
            'items.*.ris_unit.max' => 'Must not exceed 20 characters.',
            'items.*.ris_items_descrip.required' => 'Description is required.',
            'items.*.ris_items_descrip.min' => 'Must be at least 2 characters.',
            'items.*.ris_items_descrip.max' => 'Must not exceed 50 characters.',
            'items.*.ris_quantity.integer' => 'Must be an integer.',
            'items.*.ris_quantity.min' => 'Must be at least 1.',
            'items.*.ris_quantity.max' => 'Exceeds maximum limit.',
            'items.*.ris_issued_quantity.required' => 'Issued Qty is required.',
            'items.*.ris_issued_quantity.integer' => 'Must be an integer.',
            'items.*.ris_issued_quantity.min' => 'Must be at least 1.',
            'items.*.ris_issued_quantity.max' => 'Exceeds maximum limit.',
            'items.*.ris_issued_remarks.max' => 'Must not exceed 50 characters.',
            'items.*.specification.required' => 'Specification is required.',
            'items.*.specification.min' => 'Specification must be at least 5 characters.',
            'items.*.specification.max' => 'Specification must not exceed 500 characters.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_document', 'doc-ris-' . $ris->ris_id);
        }

        $validated = $validator->validated();

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

            if (isset($validated['items']) && is_array($validated['items'])) {
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

                    $spec = $risItem->risSpecs()->first();
                    if (!empty($itemData['specification'])) {
                        if ($spec) {
                            $spec->update(['ris_spec_description' => $itemData['specification']]);
                        } else {
                            $risItem->risSpecs()->create(['ris_spec_description' => $itemData['specification']]);
                        }
                    } else {
                        if ($spec) {
                            $spec->delete();
                        }
                    }
                }
            }

            // Delete items that were removed in the UI (not present in incoming request)
            RisItem::where('ris_id_fk', $ris->ris_id)
                ->whereNotIn('ris_items_id', $incomingItemIds)
                ->delete();

            DB::commit();

            $message = $intent === 'Done'
                ? 'Requisition and Issue Slip saved and exported successfully.'
                : 'Requisition and Issue Slip saved as draft.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'active_document' => 'doc-ris-' . $ris->ris_id,
                    'download_pdf' => $request->input('export_pdf') === '1' ? route('export.ris.pdf', $ris->ris_id) : null
                ]);
            }

            $response = redirect()->back()
                ->with('success', $message)
                ->with('active_document', 'doc-ris-' . $ris->ris_id);

            if ($request->input('export_pdf') === '1') {
                $response->with('download_pdf', route('export.ris.pdf', $ris->ris_id));
            }

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save Requisition and Issue Slip: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to save Requisition and Issue Slip: ' . $e->getMessage())
                ->with('active_document', 'doc-ris-' . $ris->ris_id);
        }
    }

    public function saveRsmi($rsmi_id, Request $request)
    {
        $rsmi = Rsmi::findOrFail($rsmi_id);

        $intent = $request->input('export_pdf') === '1' ? 'Done' : 'Draft';

        if ($intent === 'Done') {
            $rules = [
                'rsmi_fund_cluster' => 'required|string|min:1|max:50',
                'rsmi_serial_no' => 'required|string|min:2|max:50',
                'rsmi_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.rsmi_items_id' => 'nullable|integer',
                'items.*.rsmi_ris_no' => 'required|string|min:1|max:20',
                'items.*.rsmi_center_code' => 'required|string|min:1|max:20',
                'items.*.rsmi_stock_no' => 'nullable|string|min:1|max:20',
                'items.*.rsmi_items_descrip' => 'required|string|min:5|max:50',
                'items.*.rsmi_unit' => 'required|string|min:1|max:20',
                'items.*.rsmi_quantity' => 'required|integer|min:1|max:9999999',
                'items.*.rsmi_unit_cost' => 'required|numeric|min:1|max:9999999',
                'items.*.specification' => 'required|string|min:10|max:500',
            ];
        } else {
            $rules = [
                'rsmi_fund_cluster' => 'nullable|string|min:1|max:50',
                'rsmi_serial_no' => 'nullable|string|min:2|max:50',
                'rsmi_date' => 'nullable|date',
                'items' => 'nullable|array',
                'items.*.rsmi_items_id' => 'nullable|integer',
                'items.*.rsmi_ris_no' => 'nullable|string|min:1|max:20',
                'items.*.rsmi_center_code' => 'nullable|string|min:1|max:20',
                'items.*.rsmi_stock_no' => 'nullable|string|min:1|max:20',
                'items.*.rsmi_items_descrip' => 'nullable|string|min:5|max:50',
                'items.*.rsmi_unit' => 'nullable|string|min:1|max:20',
                'items.*.rsmi_quantity' => 'nullable|integer|min:1|max:9999999',
                'items.*.rsmi_unit_cost' => 'nullable|numeric|min:1|max:9999999',
                'items.*.specification' => 'nullable|string|max:500',
            ];
        }

        $messages = [
            'rsmi_fund_cluster.required' => 'Fund cluster is required.',
            'rsmi_fund_cluster.min' => 'Must be at least 1 character.',
            'rsmi_fund_cluster.max' => 'Must not exceed 50 characters.',
            'rsmi_serial_no.required' => 'Serial number is required.',
            'rsmi_serial_no.min' => 'Must be at least 2 characters.',
            'rsmi_serial_no.max' => 'Must not exceed 50 characters.',
            'rsmi_date.required' => 'Date is required.',
            'rsmi_date.date' => 'Must be a valid date.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.rsmi_ris_no.required' => 'RIS No. is required.',
            'items.*.rsmi_ris_no.min' => 'Must be at least 1 character.',
            'items.*.rsmi_ris_no.max' => 'Must not exceed 20 characters.',
            'items.*.rsmi_center_code.required' => 'Center code is required.',
            'items.*.rsmi_center_code.min' => 'Must be at least 1 character.',
            'items.*.rsmi_center_code.max' => 'Must not exceed 20 characters.',
            'items.*.rsmi_stock_no.min' => 'Must be at least 1 character.',
            'items.*.rsmi_stock_no.max' => 'Must not exceed 20 characters.',
            'items.*.rsmi_items_descrip.required' => 'Description is required.',
            'items.*.rsmi_items_descrip.min' => 'Must be at least 5 characters.',
            'items.*.rsmi_items_descrip.max' => 'Must not exceed 50 characters.',
            'items.*.rsmi_unit.required' => 'Unit is required.',
            'items.*.rsmi_unit.min' => 'Must be at least 1 character.',
            'items.*.rsmi_unit.max' => 'Must not exceed 20 characters.',
            'items.*.rsmi_quantity.required' => 'Quantity is required.',
            'items.*.rsmi_quantity.integer' => 'Must be an integer.',
            'items.*.rsmi_quantity.min' => 'Must be at least 1.',
            'items.*.rsmi_quantity.max' => 'Exceeds maximum limit.',
            'items.*.rsmi_unit_cost.required' => 'Unit cost is required.',
            'items.*.rsmi_unit_cost.numeric' => 'Must be a number.',
            'items.*.rsmi_unit_cost.min' => 'Must be at least 1.',
            'items.*.rsmi_unit_cost.max' => 'Exceeds maximum limit.',
            'items.*.specification.required' => 'Specification is required.',
            'items.*.specification.min' => 'Specification must be at least 10 characters.',
            'items.*.specification.max' => 'Specification must not exceed 500 characters.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_document', 'doc-rsmi-' . $rsmi->rsmi_id);
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $rsmiTotal = 0;
            $incomingItemIds = [];

            if (isset($validated['items']) && is_array($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $qty = isset($itemData['rsmi_quantity']) ? intval($itemData['rsmi_quantity']) : 0;
                    $unitCost = isset($itemData['rsmi_unit_cost']) ? floatval($itemData['rsmi_unit_cost']) : 0.0;
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

                    $spec = $rsmiItem->rsmiSpecs()->first();
                    if (!empty($itemData['specification'])) {
                        if ($spec) {
                            $spec->update(['rsmi_spec_description' => $itemData['specification']]);
                        } else {
                            $rsmiItem->rsmiSpecs()->create(['rsmi_spec_description' => $itemData['specification']]);
                        }
                    } else {
                        if ($spec) {
                            $spec->delete();
                        }
                    }
                }
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

            $message = $intent === 'Done'
                ? 'Report of Supplies and Materials Issued saved and exported successfully.'
                : 'Report of Supplies and Materials Issued saved as draft.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'active_document' => 'doc-rsmi-' . $rsmi->rsmi_id,
                    'download_pdf' => $request->input('export_pdf') === '1' ? route('export.rsmi.pdf', $rsmi->rsmi_id) : null
                ]);
            }

            $response = redirect()->back()
                ->with('success', $message)
                ->with('active_document', 'doc-rsmi-' . $rsmi->rsmi_id);

            if ($request->input('export_pdf') === '1') {
                $response->with('download_pdf', route('export.rsmi.pdf', $rsmi->rsmi_id));
            }

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save Report of Supplies and Materials Issued: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to save Report of Supplies and Materials Issued: ' . $e->getMessage())
                ->with('active_document', 'doc-rsmi-' . $rsmi->rsmi_id);
        }
    }

    public function saveIcs($ics_id, Request $request)
    {
        $ics = Ics::findOrFail($ics_id);

        $intent = $request->input('export_pdf') === '1' ? 'Done' : 'Draft';

        if ($intent === 'Done') {
            $rules = [
                'ics_fund_cluster' => 'required|string|min:1|max:50',
                'ics_no' => 'required|string|min:2|max:50',
                'ics_code_no' => 'required|string|min:1|max:50',
                'ics_received_from_date' => 'required|date',
                'ics_received_by_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.ics_items_id' => 'nullable|integer',
                'items.*.ics_quantity' => 'required|integer|min:1|max:9999999',
                'items.*.ics_unit' => 'required|string|min:2|max:20',
                'items.*.ics_unit_cost' => 'required|numeric|min:1|max:9999999',
                'items.*.ics_items_descrip' => 'required|string|min:5|max:50',
                'items.*.ics_inventory_item_no' => 'required|string|min:2|max:20',
                'items.*.ics_estimated_useful_life' => 'nullable|string|min:2|max:20',
                'items.*.specification' => 'required|string|min:5|max:500',
            ];
        } else {
            $rules = [
                'ics_fund_cluster' => 'nullable|string|min:1|max:50',
                'ics_no' => 'nullable|string|min:2|max:50',
                'ics_code_no' => 'nullable|string|min:1|max:50',
                'ics_received_from_date' => 'nullable|date',
                'ics_received_by_date' => 'nullable|date',
                'items' => 'nullable|array',
                'items.*.ics_items_id' => 'nullable|integer',
                'items.*.ics_quantity' => 'nullable|integer|min:1|max:9999999',
                'items.*.ics_unit' => 'nullable|string|min:2|max:20',
                'items.*.ics_unit_cost' => 'nullable|numeric|min:1|max:9999999',
                'items.*.ics_items_descrip' => 'nullable|string|min:5|max:50',
                'items.*.ics_inventory_item_no' => 'nullable|string|min:2|max:20',
                'items.*.ics_estimated_useful_life' => 'nullable|string|min:2|max:20',
                'items.*.specification' => 'nullable|string|max:500',
            ];
        }

        $messages = [
            'ics_fund_cluster.required' => 'Fund Cluster is required.',
            'ics_fund_cluster.min' => 'Fund Cluster must be at least 1 character.',
            'ics_fund_cluster.max' => 'Fund Cluster must not exceed 50 characters.',
            'ics_no.required' => 'ICS No. is required.',
            'ics_no.min' => 'ICS No. must be at least 2 characters.',
            'ics_no.max' => 'ICS No. must not exceed 50 characters.',
            'ics_code_no.required' => 'Code No. is required.',
            'ics_code_no.min' => 'Code No. must be at least 1 character.',
            'ics_code_no.max' => 'Code No. must not exceed 50 characters.',
            'ics_received_from_date.required' => 'Received from Date is required.',
            'ics_received_from_date.date' => 'Must be a valid date.',
            'ics_received_by_date.required' => 'Received by Date is required.',
            'ics_received_by_date.date' => 'Must be a valid date.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.ics_quantity.required' => 'Quantity is required.',
            'items.*.ics_quantity.integer' => 'Quantity must be an integer.',
            'items.*.ics_quantity.min' => 'Quantity must be at least 1.',
            'items.*.ics_quantity.max' => 'Quantity exceeds maximum limit.',
            'items.*.ics_unit.required' => 'Unit is required.',
            'items.*.ics_unit.min' => 'Unit must be at least 2 characters.',
            'items.*.ics_unit.max' => 'Unit must not exceed 20 characters.',
            'items.*.ics_unit_cost.required' => 'Unit Cost is required.',
            'items.*.ics_unit_cost.numeric' => 'Unit Cost must be a number.',
            'items.*.ics_unit_cost.min' => 'Unit Cost must be at least 1.',
            'items.*.ics_unit_cost.max' => 'Unit Cost exceeds maximum limit.',
            'items.*.ics_items_descrip.required' => 'Description is required.',
            'items.*.ics_items_descrip.min' => 'Description must be at least 5 characters.',
            'items.*.ics_items_descrip.max' => 'Description must not exceed 50 characters.',
            'items.*.ics_inventory_item_no.required' => 'This is required.',
            'items.*.ics_inventory_item_no.min' => 'Must be at least 2 characters.',
            'items.*.ics_inventory_item_no.max' => 'Must not exceed 20 characters.',
            'items.*.ics_estimated_useful_life.min' => 'Must be at least 2 characters.',
            'items.*.ics_estimated_useful_life.max' => 'Must not exceed 20 characters.',
            'items.*.specification.required' => 'Specification is required.',
            'items.*.specification.min' => 'Specification must be at least 5 characters.',
            'items.*.specification.max' => 'Specification must not exceed 500 characters.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_document', 'doc-ics-' . $ics->ics_id);
        }

        $validated = $validator->validated();

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

            if (isset($validated['items']) && is_array($validated['items'])) {
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

                    $spec = $icsItem->icsSpecs()->first();
                    if (!empty($itemData['specification'])) {
                        if ($spec) {
                            $spec->update(['ics_spec_description' => $itemData['specification']]);
                        } else {
                            $icsItem->icsSpecs()->create(['ics_spec_description' => $itemData['specification']]);
                        }
                    } else {
                        if ($spec) {
                            $spec->delete();
                        }
                    }
                }
            }

            // Delete items that were removed in the UI (not present in incoming request)
            IcsItem::where('ics_id_fk', $ics->ics_id)
                ->whereNotIn('ics_items_id', $incomingItemIds)
                ->delete();

            DB::commit();

            $message = $intent === 'Done'
                ? 'Inventory Custodian Slip saved and exported successfully.'
                : 'Inventory Custodian Slip saved as draft.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'active_document' => 'doc-ics-' . $ics->ics_id,
                    'download_pdf' => $request->input('export_pdf') === '1' ? route('export.ics.pdf', $ics->ics_id) : null
                ]);
            }

            $response = redirect()->back()
                ->with('success', $message)
                ->with('active_document', 'doc-ics-' . $ics->ics_id);

            if ($request->input('export_pdf') === '1') {
                $response->with('download_pdf', route('export.ics.pdf', $ics->ics_id));
            }

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save Inventory Custodian Slip: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to save Inventory Custodian Slip: ' . $e->getMessage())
                ->with('active_document', 'doc-ics-' . $ics->ics_id);
        }
    }

    public function saveRspi($rspi_id, Request $request)
    {
        $rspi = Rspi::findOrFail($rspi_id);

        $intent = $request->input('export_pdf') === '1' ? 'Done' : 'Draft';

        if ($intent === 'Done') {
            $rules = [
                'rspi_fund_cluster' => 'required|string|min:1|max:50',
                'rspi_serial_no' => 'required|string|min:2|max:50',
                'rspi_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.rspi_items_id' => 'nullable|integer',
                'items.*.rspi_ics_no' => 'required|string|min:1|max:20',
                'items.*.rspi_center_code' => 'required|string|min:1|max:20',
                'items.*.rspi_property_no' => 'required|string|min:1|max:20',
                'items.*.rspi_items_descrip' => 'required|string|min:5|max:50',
                'items.*.rspi_unit' => 'required|string|min:2|max:20',
                'items.*.rspi_quantity' => 'required|integer|min:1|max:9999999',
                'items.*.rspi_unit_cost' => 'required|numeric|min:1|max:9999999',
                'items.*.specification' => 'required|string|min:5|max:500',
            ];
        } else {
            $rules = [
                'rspi_fund_cluster' => 'nullable|string|max:50',
                'rspi_serial_no' => 'nullable|string|max:50',
                'rspi_date' => 'nullable|date',
                'items' => 'nullable|array',
                'items.*.rspi_items_id' => 'nullable|integer',
                'items.*.rspi_ics_no' => 'nullable|string|max:20',
                'items.*.rspi_center_code' => 'nullable|string|max:20',
                'items.*.rspi_property_no' => 'nullable|string|max:20',
                'items.*.rspi_items_descrip' => 'nullable|string|max:50',
                'items.*.rspi_unit' => 'nullable|string|max:20',
                'items.*.rspi_quantity' => 'nullable|integer|min:1|max:9999999',
                'items.*.rspi_unit_cost' => 'nullable|numeric|min:1|max:9999999',
                'items.*.specification' => 'nullable|string|max:500',
            ];
        }

        $messages = [
            'rspi_fund_cluster.required' => 'Fund Cluster is required.',
            'rspi_fund_cluster.max' => 'Must not exceed 50 characters.',
            'rspi_serial_no.required' => 'Serial No. is required.',
            'rspi_serial_no.min' => 'Must be at least 2 characters.',
            'rspi_serial_no.max' => 'Must not exceed 50 characters.',
            'rspi_date.required' => 'Date is required.',
            'rspi_date.date' => 'Must be a valid date.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.rspi_ics_no.required' => 'ICS No. is required.',
            'items.*.rspi_ics_no.max' => 'Must not exceed 20 characters.',
            'items.*.rspi_center_code.required' => 'Center Code is required.',
            'items.*.rspi_center_code.max' => 'Must not exceed 20 characters.',
            'items.*.rspi_property_no.required' => 'Property No. is required.',
            'items.*.rspi_property_no.max' => 'Must not exceed 20 characters.',
            'items.*.rspi_items_descrip.required' => 'Description is required.',
            'items.*.rspi_items_descrip.min' => 'Must be at least 5 characters.',
            'items.*.rspi_items_descrip.max' => 'Must not exceed 50 characters.',
            'items.*.rspi_unit.required' => 'Unit is required.',
            'items.*.rspi_unit.max' => 'Must not exceed 20 characters.',
            'items.*.rspi_quantity.required' => 'Quantity is required.',
            'items.*.rspi_quantity.integer' => 'Must be an integer.',
            'items.*.rspi_quantity.min' => 'Must be at least 1.',
            'items.*.rspi_quantity.max' => 'Exceeds maximum limit.',
            'items.*.rspi_unit_cost.required' => 'Unit Cost is required.',
            'items.*.rspi_unit_cost.numeric' => 'Must be a number.',
            'items.*.rspi_unit_cost.min' => 'Must be at least 1.',
            'items.*.rspi_unit_cost.max' => 'Exceeds maximum limit.',
            'items.*.specification.required' => 'Specification is required.',
            'items.*.specification.min' => 'Specification must be at least 5 characters.',
            'items.*.specification.max' => 'Specification must not exceed 500 characters.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_document', 'doc-rspi-' . $rspi->rspi_id);
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $rspiTotal = 0;
            $incomingItemIds = [];

            if (isset($validated['items']) && is_array($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $qty = isset($itemData['rspi_quantity']) ? intval($itemData['rspi_quantity']) : 0;
                    $unitCost = isset($itemData['rspi_unit_cost']) ? floatval($itemData['rspi_unit_cost']) : 0;
                    $amount = $qty * $unitCost;
                    $rspiTotal += $amount;

                    if (!empty($itemData['rspi_items_id'])) {
                        // Update existing item
                        $rspiItem = RspiItem::where('rspi_id_fk', $rspi->rspi_id)
                            ->findOrFail($itemData['rspi_items_id']);

                        $rspiItem->update([
                            'rspi_ics_no' => $itemData['rspi_ics_no'] ?? null,
                            'rspi_center_code' => $itemData['rspi_center_code'] ?? null,
                            'rspi_property_no' => $itemData['rspi_property_no'] ?? null,
                            'rspi_items_descrip' => $itemData['rspi_items_descrip'] ?? null,
                            'rspi_unit' => $itemData['rspi_unit'] ?? null,
                            'rspi_quantity' => $qty,
                            'rspi_unit_cost' => $unitCost,
                            'rspi_amount' => $amount,
                        ]);

                        $incomingItemIds[] = $rspiItem->rspi_items_id;
                    } else {
                        // Create new item
                        $rspiItem = RspiItem::create([
                            'rspi_id_fk' => $rspi->rspi_id,
                            'rspi_ics_no' => $itemData['rspi_ics_no'] ?? null,
                            'rspi_center_code' => $itemData['rspi_center_code'] ?? null,
                            'rspi_property_no' => $itemData['rspi_property_no'] ?? null,
                            'rspi_items_descrip' => $itemData['rspi_items_descrip'] ?? null,
                            'rspi_unit' => $itemData['rspi_unit'] ?? null,
                            'rspi_quantity' => $qty,
                            'rspi_unit_cost' => $unitCost,
                            'rspi_amount' => $amount,
                        ]);

                        $incomingItemIds[] = $rspiItem->rspi_items_id;
                    }

                    $spec = $rspiItem->rspiSpecs()->first();
                    if (!empty($itemData['specification'])) {
                        if ($spec) {
                            $spec->update(['rspi_spec_description' => $itemData['specification']]);
                        } else {
                            $rspiItem->rspiSpecs()->create(['rspi_spec_description' => $itemData['specification']]);
                        }
                    } else {
                        if ($spec) {
                            $spec->delete();
                        }
                    }
                }
            }

            // Update RSPI Header
            $rspi->update([
                'rspi_fund_cluster' => $validated['rspi_fund_cluster'] ?? null,
                'rspi_serial_no' => $validated['rspi_serial_no'] ?? null,
                'rspi_date' => $validated['rspi_date'] ?? null,
                'rspi_total' => $rspiTotal,
            ]);

            // Delete items that were removed in the UI (not present in incoming request)
            RspiItem::where('rspi_id_fk', $rspi->rspi_id)
                ->whereNotIn('rspi_items_id', $incomingItemIds)
                ->delete();

            DB::commit();

            $message = $intent === 'Done'
                ? 'Report of Semi-Expendable Property Issued saved and exported successfully.'
                : 'Report of Semi-Expendable Property Issued saved as draft.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'active_document' => 'doc-rspi-' . $rspi->rspi_id,
                    'download_pdf' => $request->input('export_pdf') === '1' ? route('export.rspi.pdf', $rspi->rspi_id) : null
                ]);
            }

            $response = redirect()->back()
                ->with('success', $message)
                ->with('active_document', 'doc-rspi-' . $rspi->rspi_id);

            if ($request->input('export_pdf') === '1') {
                $response->with('download_pdf', route('export.rspi.pdf', $rspi->rspi_id));
            }

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save Report of Semi-Expendable Property Issued: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to save Report of Semi-Expendable Property Issued: ' . $e->getMessage())
                ->with('active_document', 'doc-rspi-' . $rspi->rspi_id);
        }
    }

    public function savePar($par_id, Request $request)
    {
        $par = Par::findOrFail($par_id);
        $intent = $request->input('export_pdf') === '1' ? 'Done' : 'Draft';

        if ($intent === 'Done') {
            $rules = [
                'par_fund_cluster' => 'required|string|min:1|max:50',
                'par_no' => 'required|string|min:2|max:50',
                'par_code' => 'required|string|min:2|max:50',
                'par_received_by_date' => 'required|date',
                'par_issued_by_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.par_items_id' => 'nullable|integer',
                'items.*.par_po_items_id_fk' => 'nullable|integer|exists:po_items_tbl,po_items_id',
                'items.*.par_quantity' => 'required|integer|min:1|max:9999999',
                'items.*.par_unit' => 'required|string|min:2|max:20',
                'items.*.par_items_descrip' => 'required|string|min:5|max:50',
                'items.*.par_property_no' => 'required|string|min:1|max:20',
                'items.*.par_date_acquired' => 'required|date',
                'items.*.par_amount' => 'required|numeric|min:1|max:9999999',
                'items.*.specification' => 'required|string|min:5|max:500',
            ];
        } else {
            $rules = [
                'par_fund_cluster' => 'nullable|string|min:1|max:50',
                'par_no' => 'nullable|string|min:2|max:50',
                'par_code' => 'nullable|string|min:2|max:50',
                'par_received_by_date' => 'nullable|date',
                'par_issued_by_date' => 'nullable|date',
                'items' => 'nullable|array',
                'items.*.par_items_id' => 'nullable|integer',
                'items.*.par_po_items_id_fk' => 'nullable|integer|exists:po_items_tbl,po_items_id',
                'items.*.par_quantity' => 'nullable|integer|min:1|max:9999999',
                'items.*.par_unit' => 'nullable|string|min:1|max:20',
                'items.*.par_items_descrip' => 'nullable|string|min:5|max:50',
                'items.*.par_property_no' => 'nullable|string|min:1|max:20',
                'items.*.par_date_acquired' => 'nullable|date',
                'items.*.par_amount' => 'nullable|numeric|min:1|max:9999999',
                'items.*.specification' => 'nullable|string|max:500',
            ];
        }

        $messages = [
            'par_fund_cluster.required' => 'Fund Cluster is required.',
            'par_fund_cluster.min' => 'Must be at least 1 character.',
            'par_fund_cluster.max' => 'Must not exceed 50 characters.',
            'par_no.required' => 'PAR No. is required.',
            'par_no.min' => 'Must be at least 2 characters.',
            'par_no.max' => 'Must not exceed 50 characters.',
            'par_code.required' => 'Code is required.',
            'par_code.min' => 'Must be at least 2 characters.',
            'par_code.max' => 'Must not exceed 50 characters.',
            'par_received_by_date.required' => 'Date is required.',
            'par_received_by_date.date' => 'Must be a valid date.',
            'par_issued_by_date.required' => 'Date is required.',
            'par_issued_by_date.date' => 'Must be a valid date.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.par_quantity.required' => 'Qty. is required.',
            'items.*.par_quantity.integer' => 'Must be an integer.',
            'items.*.par_quantity.min' => 'Must be at least 1.',
            'items.*.par_quantity.max' => 'Exceeds maximum limit.',
            'items.*.par_unit.required' => 'Unit is required.',
            'items.*.par_unit.min' => 'Must be at least 2 character.',
            'items.*.par_unit.max' => 'Must not exceed 20 characters.',
            'items.*.par_items_descrip.required' => 'Description is required.',
            'items.*.par_items_descrip.min' => 'Must be at least 5 characters.',
            'items.*.par_items_descrip.max' => 'Must not exceed 50 characters.',
            'items.*.par_property_no.required' => 'Property No. is required.',
            'items.*.par_property_no.min' => 'Must be at least 1 character.',
            'items.*.par_property_no.max' => 'Must not exceed 20 characters.',
            'items.*.par_date_acquired.required' => 'Date Required is required.',
            'items.*.par_date_acquired.date' => 'Must be a valid date.',
            'items.*.par_amount.required' => 'Amount is required.',
            'items.*.par_amount.numeric' => 'Must be a number.',
            'items.*.par_amount.min' => 'Must be at least 1.',
            'items.*.par_amount.max' => 'Exceeds maximum limit.',
            'items.*.specification.required' => 'Specification is required.',
            'items.*.specification.min' => 'Specification must be at least 5 characters.',
            'items.*.specification.max' => 'Specification must not exceed 500 characters.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_document', 'doc-par-' . $par->par_id);
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $par->update([
                'par_fund_cluster' => $validated['par_fund_cluster'] ?? null,
                'par_no' => $validated['par_no'] ?? null,
                'par_code' => $validated['par_code'] ?? null,
                'par_received_by_date' => $validated['par_received_by_date'] ?? null,
                'par_issued_by_date' => $validated['par_issued_by_date'] ?? null,
            ]);

            $incomingItemIds = [];

            if (isset($validated['items']) && is_array($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $qty = isset($itemData['par_quantity']) ? intval($itemData['par_quantity']) : null;
                    $amount = isset($itemData['par_amount']) ? floatval($itemData['par_amount']) : null;

                    if (!empty($itemData['par_items_id'])) {
                        // Update existing item
                        $parItem = ParItem::where('par_id_fk', $par->par_id)
                            ->findOrFail($itemData['par_items_id']);

                        $parItem->update([
                            'par_po_items_id_fk' => $itemData['par_po_items_id_fk'] ?? null,
                            'par_quantity' => $qty,
                            'par_unit' => $itemData['par_unit'] ?? null,
                            'par_items_descrip' => $itemData['par_items_descrip'] ?? null,
                            'par_property_no' => $itemData['par_property_no'] ?? null,
                            'par_date_acquired' => $itemData['par_date_acquired'] ?? null,
                            'par_amount' => $amount,
                        ]);

                        $incomingItemIds[] = $parItem->par_items_id;
                    } else {
                        // Create new item
                        $parItem = ParItem::create([
                            'par_id_fk' => $par->par_id,
                            'par_po_items_id_fk' => $itemData['par_po_items_id_fk'] ?? null,
                            'par_quantity' => $qty,
                            'par_unit' => $itemData['par_unit'] ?? null,
                            'par_items_descrip' => $itemData['par_items_descrip'] ?? null,
                            'par_property_no' => $itemData['par_property_no'] ?? null,
                            'par_date_acquired' => $itemData['par_date_acquired'] ?? null,
                            'par_amount' => $amount,
                        ]);

                        $incomingItemIds[] = $parItem->par_items_id;
                    }

                    $spec = $parItem->parSpecs()->first();
                    if (!empty($itemData['specification'])) {
                        if ($spec) {
                            $spec->update(['par_spec_description' => $itemData['specification']]);
                        } else {
                            $parItem->parSpecs()->create(['par_spec_description' => $itemData['specification']]);
                        }
                    } else {
                        if ($spec) {
                            $spec->delete();
                        }
                    }
                }
            }

            // Remove deleted items
            ParItem::where('par_id_fk', $par->par_id)
                ->whereNotIn('par_items_id', $incomingItemIds)
                ->delete();

            DB::commit();

            $message = $intent === 'Done'
                ? 'Property Acknowledgement Receipt saved and exported successfully.'
                : 'Property Acknowledgement Receipt saved as draft.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'active_document' => 'doc-par-' . $par->par_id,
                    'download_pdf' => $request->input('export_pdf') === '1' ? route('export.par.pdf', $par->par_id) : null
                ]);
            }

            $response = redirect()->back()
                ->with('success', $message)
                ->with('active_document', 'doc-par-' . $par->par_id);

            if ($request->input('export_pdf') === '1') {
                $response->with('download_pdf', route('export.par.pdf', $par->par_id));
            }

            return $response;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save Property Acknowledgement Receipt: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to save Property Acknowledgement Receipt: ' . $e->getMessage())
                ->with('active_document', 'doc-par-' . $par->par_id);
        }
    }
}
