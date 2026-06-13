<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoParent;
use App\Models\PoItem;
use App\Models\User;
use App\Models\Iar;
use App\Models\IarItem;
use App\Models\IarItemSpec;
use App\Models\Ris;
use App\Models\RisItem;
use App\Models\RisItemSpec;
use App\Models\Rsmi;
use App\Models\RsmiItem;
use App\Models\RsmiItemSpec;
use App\Models\Ics;
use App\Models\IcsItem;
use App\Models\IcsItemSpec;
use App\Models\Rspi;
use App\Models\RspiItem;
use App\Models\RspiItemSpec;
use App\Models\Par;
use App\Models\ParItem;
use App\Models\ParItemSpec;
use App\Models\Ndr;
use App\Models\NdrItem;
use App\Models\NdrItemSpec;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class PoReviewController extends Controller
{
    public function showPoReview($po_id)
    {
        $po = PoParent::with(['poItems.poSpecs', 'savedBy'])->findOrFail($po_id);

        // Redirect if delivery attachments already exist
        if (
            $po->iarReports()->exists() ||
            $po->risSlips()->exists() ||
            $po->icsSlips()->exists() ||
            $po->parReceipts()->exists() ||
            $po->rsmiReports()->exists() ||
            $po->rspiReports()->exists()
        ) {
            return redirect()->route('show.delivery.attachment', ['po_id' => $po_id]);
        }

        $breadcrumbs = [
            ['title' => 'Procurement', 'url' => route('show.procure')],
            ['title' => 'PO Review', 'url' => '']
        ];

        $users = User::all();
        $departments = Department::orderBy('dep_name')->get();

        return view('supply.pages.supply-po-review', compact('po', 'breadcrumbs', 'users', 'departments'));
    }

    public function generateAttachments($po_id, Request $request)
    {
        $po = PoParent::findOrFail($po_id);
        $itemsData = $request->input('items', []);

        // Collect all department IDs from the request distributions beforehand
        $deptIds = [];
        foreach ($itemsData as $entry) {
            if (($entry['category'] ?? '') === 'Supply and Materials') {
                foreach ($entry['distributions'] ?? [] as $dist) {
                    if (!empty($dist['dept_id'])) {
                        $deptIds[] = $dist['dept_id'];
                    }
                }
            }
        }

        // Query all departments at once to avoid N+1 queries
        $departments = [];
        if (!empty($deptIds)) {
            $departments = Department::whereIn('dep_id', array_unique($deptIds))
                ->get()
                ->keyBy('dep_id');
        }

        DB::beginTransaction();
        try {
            $supplyItems = [];
            $semiExpendableItems = [];
            $equipmentItems = [];
            $notDeliveredItems = [];

            foreach ($itemsData as $entry) {
                $itemId = $entry['po_items_id'];
                $category = $entry['category'];
                $dists = $entry['distributions'] ?? [];

                $poItem = PoItem::with('poSpecs')->where('po_id_fk', $po->po_id)->findOrFail($itemId);

                // Update category in DB
                $poItem->update([
                    'po_items_category' => $category
                ]);

                if ($category === 'Supply and Materials') {
                    $supplyItems[] = [
                        'item' => $poItem,
                        'distributions' => $dists
                    ];
                } elseif ($category === 'Semi-Expendable') {
                    $semiExpendableItems[] = [
                        'item' => $poItem,
                        'distributions' => $dists
                    ];
                } elseif ($category === 'Equipment') {
                    $equipmentItems[] = [
                        'item' => $poItem,
                        'distributions' => $dists
                    ];
                } elseif ($category === 'Not Delivered') {
                    $notDeliveredItems[] = [
                        'item' => $poItem,
                        'distributions' => $dists
                    ];
                }
            }

            // 1. Supply and Materials
            if (count($supplyItems) > 0) {
                $deptNames = [];
                foreach ($supplyItems as $itemData) {
                    if (isset($itemData['distributions']) && is_array($itemData['distributions'])) {
                        foreach ($itemData['distributions'] as $dist) {
                            $deptName = '';
                            if (!empty($dist['dept_id']) && isset($departments[$dist['dept_id']])) {
                                $deptName = $departments[$dist['dept_id']]->dep_name;
                            }
                            if (empty($deptName) && !empty($dist['dept_name'])) {
                                $deptName = trim($dist['dept_name']);
                            }
                            if (!empty($deptName)) {
                                $deptNames[] = $deptName;
                            }
                        }
                    }
                }
                $uniqueDepts = array_unique($deptNames);
                $iarOffice = implode(', ', $uniqueDepts);

                if (empty($iarOffice)) {
                    $iarOffice = $po->purchaseRequest->department->dep_name ?? '';
                }

                $iar = Iar::create([
                    'iar_po_id_fk' => $po->po_id,
                    'iar_supplier' => $po->po_supplier,
                    'iar_po_no' => $po->po_no,
                    'iar_po_no_date' => $po->po_no . ' / ' . $po->po_date,
                    'iar_office' => $iarOffice,
                    'iar_acceptance_type' => 'Complete',
                    'iar_accepted_by' => auth()->user()->user_id ?? null,
                ]);

                foreach ($supplyItems as $itemData) {
                    $iarItem = IarItem::create([
                        'iar_id_fk' => $iar->iar_id,
                        'iar_po_items_id_fk' => $itemData['item']->po_items_id,
                        'iar_stock_no' => $itemData['item']->po_items_stockno,
                        'iar_unit' => $itemData['item']->po_items_unit,
                        'iar_items_descrip' => $itemData['item']->po_items_descrip,
                        'iar_quantity' => $itemData['item']->po_items_quantity,
                        'iar_unit_cost' => $itemData['item']->po_items_cost,
                        'iar_amount' => $itemData['item']->po_items_quantity * $itemData['item']->po_items_cost,
                    ]);

                    foreach ($itemData['item']->poSpecs as $poSpec) {
                        IarItemSpec::create([
                            'iar_items_id_fk' => $iarItem->iar_items_id,
                            'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                            'iar_spec_description' => $poSpec->po_spec_description,
                        ]);
                    }
                }

                $deptDists = [];
                foreach ($supplyItems as $itemData) {
                    foreach ($itemData['distributions'] as $dist) {
                        $deptName = '';
                        if (!empty($dist['dept_id']) && isset($departments[$dist['dept_id']])) {
                            $deptName = $departments[$dist['dept_id']]->dep_name;
                        }
                        if (empty($deptName) && !empty($dist['dept_name'])) {
                            $deptName = trim($dist['dept_name']);
                        }
                        if (empty($deptName)) {
                            $deptName = 'Unknown';
                        }
                        $deptDists[$deptName][] = [
                            'item' => $itemData['item'],
                            'qty' => $dist['qty']
                        ];
                    }
                }

                $rsmi = Rsmi::create([
                    'po_id_fk' => $po->po_id,
                    'rsmi_fund_cluster' => $po->po_fund_cluster,
                    'rsmi_po_no' => $po->po_no,
                    'rsmi_user_id_fk' => auth()->user()->user_id ?? null,
                    'rsmi_total' => 0
                ]);
                $rsmiTotal = 0;

                foreach ($deptDists as $deptName => $lines) {
                    $ris = Ris::create([
                        'po_id_fk' => $po->po_id,
                        'ris_fund_cluster' => $po->po_fund_cluster,
                        'ris_division' => $deptName,
                        'ris_office' => $deptName,
                        'ris_purpose' => $po->purchaseRequest->pr_purpose ?? '',
                        'ris_requested_by' => auth()->user()->user_id ?? null,
                        'ris_approved_by' => auth()->user()->user_id ?? null,
                        'ris_issued_by' => auth()->user()->user_id ?? null,
                    ]);

                    foreach ($lines as $line) {
                        $item = $line['item'];
                        $qty = $line['qty'];
                        $itemAmount = $qty * $item->po_items_cost;
                        $rsmiTotal += $itemAmount;

                        $risItem = RisItem::create([
                            'ris_id_fk' => $ris->ris_id,
                            'ris_po_items_id_fk' => $item->po_items_id,
                            'ris_stock_no' => $item->po_items_stockno,
                            'ris_unit' => $item->po_items_unit,
                            'ris_items_descrip' => $item->po_items_descrip,
                            'ris_quantity' => $qty,
                            'ris_stock_available' => 'Yes',
                            'ris_issued_quantity' => $qty,
                        ]);

                        foreach ($item->poSpecs as $poSpec) {
                            RisItemSpec::create([
                                'ris_items_id_fk' => $risItem->ris_items_id,
                                'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                                'ris_spec_description' => $poSpec->po_spec_description,
                            ]);
                        }

                        $rsmiItem = RsmiItem::create([
                            'rsmi_id_fk' => $rsmi->rsmi_id,
                            'rsmi_stock_no' => $item->po_items_stockno,
                            'rsmi_items_descrip' => $item->po_items_descrip,
                            'rsmi_unit' => $item->po_items_unit,
                            'rsmi_quantity' => $qty,
                            'rsmi_unit_cost' => $item->po_items_cost,
                            'rsmi_amount' => $itemAmount,
                        ]);

                        foreach ($item->poSpecs as $poSpec) {
                            RsmiItemSpec::create([
                                'rsmi_items_id_fk' => $rsmiItem->rsmi_items_id,
                                'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                                'rsmi_spec_description' => $poSpec->po_spec_description,
                            ]);
                        }
                    }
                }

                $rsmi->update([
                    'rsmi_total' => $rsmiTotal
                ]);
            }

            // 2. Semi-Expendables
            if (count($semiExpendableItems) > 0) {
                $iar = Iar::create([
                    'iar_po_id_fk' => $po->po_id,
                    'iar_fund_cluster' => $po->po_fund_cluster,
                    'iar_supplier' => $po->po_supplier,
                    'iar_po_no' => $po->po_no,
                    'iar_po_no_date' => $po->po_no . ' / ' . $po->po_date,
                    'iar_office' => $po->purchaseRequest->department->dep_name ?? '',
                    'iar_acceptance_type' => 'Complete',
                    'iar_accepted_by' => auth()->user()->user_id ?? null,
                ]);

                foreach ($semiExpendableItems as $itemData) {
                    $iarItem = IarItem::create([
                        'iar_id_fk' => $iar->iar_id,
                        'iar_po_items_id_fk' => $itemData['item']->po_items_id,
                        'iar_stock_no' => $itemData['item']->po_items_stockno,
                        'iar_unit' => $itemData['item']->po_items_unit,
                        'iar_items_descrip' => $itemData['item']->po_items_descrip,
                        'iar_quantity' => $itemData['item']->po_items_quantity,
                        'iar_unit_cost' => $itemData['item']->po_items_cost,
                        'iar_amount' => $itemData['item']->po_items_quantity * $itemData['item']->po_items_cost,
                    ]);

                    foreach ($itemData['item']->poSpecs as $poSpec) {
                        IarItemSpec::create([
                            'iar_items_id_fk' => $iarItem->iar_items_id,
                            'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                            'iar_spec_description' => $poSpec->po_spec_description,
                        ]);
                    }
                }

                $userDists = [];
                foreach ($semiExpendableItems as $itemData) {
                    foreach ($itemData['distributions'] as $dist) {
                        $userId = $dist['user_id'] ?? null;
                        if ($userId) {
                            $userDists[$userId][] = [
                                'item' => $itemData['item'],
                                'qty' => $dist['qty']
                            ];
                        }
                    }
                }

                $rspi = Rspi::create([
                    'po_id_fk' => $po->po_id,
                    'rspi_fund_cluster' => $po->po_fund_cluster,
                    'rspi_po_no' => $po->po_no,
                    'rspi_user_id_fk' => auth()->user()->user_id ?? null,
                    'rspi_total' => 0
                ]);
                $rspiTotal = 0;

                foreach ($userDists as $userId => $lines) {
                    $targetUser = User::with('departments')->find($userId);
                    $userOffice = $targetUser->departments->first()->dep_name ?? '';

                    $ris = Ris::create([
                        'po_id_fk' => $po->po_id,
                        'ris_fund_cluster' => $po->po_fund_cluster,
                        'ris_office' => $userOffice,
                        'ris_purpose' => $po->purchaseRequest->pr_purpose ?? '',
                        'ris_requested_by' => $userId,
                        'ris_approved_by' => auth()->user()->user_id ?? null,
                        'ris_issued_by' => auth()->user()->user_id ?? null,
                        'ris_received_by' => $userId,
                    ]);

                    $ics = Ics::create([
                        'po_id_fk' => $po->po_id,
                        'ics_fund_cluster' => $po->po_fund_cluster,
                        'ics_po_no' => $po->po_no,
                        'ics_received_from' => auth()->user()->user_id ?? null,
                        'ics_received_from_pos' => 'Supply Officer',
                        'ics_received_by' => $userId,
                    ]);

                    foreach ($lines as $line) {
                        $item = $line['item'];
                        $qty = $line['qty'];
                        $itemAmount = $qty * $item->po_items_cost;
                        $rspiTotal += $itemAmount;

                        $risItem = RisItem::create([
                            'ris_id_fk' => $ris->ris_id,
                            'ris_po_items_id_fk' => $item->po_items_id,
                            'ris_stock_no' => $item->po_items_stockno,
                            'ris_unit' => $item->po_items_unit,
                            'ris_items_descrip' => $item->po_items_descrip,
                            'ris_issued_quantity' => $qty,
                        ]);

                        foreach ($item->poSpecs as $poSpec) {
                            RisItemSpec::create([
                                'ris_items_id_fk' => $risItem->ris_items_id,
                                'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                                'ris_spec_description' => $poSpec->po_spec_description,
                            ]);
                        }

                        $icsItem = IcsItem::create([
                            'ics_id_fk' => $ics->ics_id,
                            'ics_quantity' => $qty,
                            'ics_unit' => $item->po_items_unit,
                            'ics_unit_cost' => $item->po_items_cost,
                            'ics_total_cost' => $itemAmount,
                            'ics_items_descrip' => $item->po_items_descrip,
                            'ics_inventory_item_no' => $item->po_items_stockno,
                        ]);

                        foreach ($item->poSpecs as $poSpec) {
                            IcsItemSpec::create([
                                'ics_items_id_fk' => $icsItem->ics_items_id,
                                'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                                'ics_spec_description' => $poSpec->po_spec_description,
                            ]);
                        }

                        $rspiItem = RspiItem::create([
                            'rspi_id_fk' => $rspi->rspi_id,
                            'rspi_property_no' => $item->po_items_stockno,
                            'rspi_items_descrip' => $item->po_items_descrip,
                            'rspi_unit' => $item->po_items_unit,
                            'rspi_quantity' => $qty,
                            'rspi_unit_cost' => $item->po_items_cost,
                            'rspi_amount' => $itemAmount,
                        ]);

                        foreach ($item->poSpecs as $poSpec) {
                            RspiItemSpec::create([
                                'rspi_items_id_fk' => $rspiItem->rspi_items_id,
                                'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                                'rspi_spec_description' => $poSpec->po_spec_description,
                            ]);
                        }
                    }
                }

                $rspi->update([
                    'rspi_total' => $rspiTotal
                ]);
            }

            // 3. Equipment
            if (count($equipmentItems) > 0) {
                $iar = Iar::create([
                    'iar_po_id_fk' => $po->po_id,
                    'iar_fund_cluster' => $po->po_fund_cluster,
                    'iar_supplier' => $po->po_supplier,
                    'iar_po_no' => $po->po_no,
                    'iar_po_no_date' => $po->po_no . ' / ' . $po->po_date,
                    'iar_office' => $po->purchaseRequest->department->dep_name ?? '',
                    'iar_acceptance_type' => 'Complete',
                    'iar_accepted_by' => auth()->user()->user_id ?? null,
                ]);

                foreach ($equipmentItems as $itemData) {
                    $iarItem = IarItem::create([
                        'iar_id_fk' => $iar->iar_id,
                        'iar_po_items_id_fk' => $itemData['item']->po_items_id,
                        'iar_stock_no' => $itemData['item']->po_items_stockno,
                        'iar_unit' => $itemData['item']->po_items_unit,
                        'iar_items_descrip' => $itemData['item']->po_items_descrip,
                        'iar_quantity' => $itemData['item']->po_items_quantity,
                        'iar_unit_cost' => $itemData['item']->po_items_cost,
                        'iar_amount' => $itemData['item']->po_items_quantity * $itemData['item']->po_items_cost,
                    ]);

                    foreach ($itemData['item']->poSpecs as $poSpec) {
                        IarItemSpec::create([
                            'iar_items_id_fk' => $iarItem->iar_items_id,
                            'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                            'iar_spec_description' => $poSpec->po_spec_description,
                        ]);
                    }
                }

                $userDists = [];
                foreach ($equipmentItems as $itemData) {
                    foreach ($itemData['distributions'] as $dist) {
                        $userId = $dist['user_id'] ?? null;
                        if ($userId) {
                            $userDists[$userId][] = [
                                'item' => $itemData['item'],
                                'qty' => $dist['qty']
                            ];
                        }
                    }
                }

                foreach ($userDists as $userId => $lines) {
                    $par = Par::create([
                        'po_id_fk' => $po->po_id,
                        'par_fund_cluster' => $po->po_fund_cluster,
                        'par_po_no' => $po->po_no,
                        'par_issued_by' => auth()->user()->user_id ?? null,
                        'par_issued_by_pos' => 'Supply Officer',
                        'par_received_by' => $userId,
                    ]);

                    foreach ($lines as $line) {
                        $item = $line['item'];
                        $qty = $line['qty'];
                        $itemAmount = $qty * $item->po_items_cost;

                        $parItem = ParItem::create([
                            'par_id_fk' => $par->par_id,
                            'par_quantity' => $qty,
                            'par_unit' => $item->po_items_unit,
                            'par_items_descrip' => $item->po_items_descrip,
                            'par_property_no' => $item->po_items_stockno,
                            'par_amount' => $itemAmount,
                        ]);

                        foreach ($item->poSpecs as $poSpec) {
                            ParItemSpec::create([
                                'par_items_id_fk' => $parItem->par_items_id,
                                'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                                'par_spec_description' => $poSpec->po_spec_description,
                            ]);
                        }
                    }
                }
            }

            // 4. Not Delivered
            if (count($notDeliveredItems) > 0) {
                $ndr = Ndr::create([
                    'po_id_fk' => $po->po_id,
                    'ndr_reported_by' => auth()->user()->user_id ?? null,
                ]);

                foreach ($notDeliveredItems as $itemData) {
                    $item = $itemData['item'];
                    $ndrItem = NdrItem::create([
                        'ndr_id_fk' => $ndr->ndr_id,
                        'ndr_po_items_id_fk' => $item->po_items_id,
                        'ndr_stock_no' => $item->po_items_stockno,
                        'ndr_unit' => $item->po_items_unit,
                        'ndr_items_descrip' => $item->po_items_descrip,
                        'ndr_quantity' => $item->po_items_quantity,
                    ]);

                    foreach ($item->poSpecs as $poSpec) {
                        NdrItemSpec::create([
                            'ndr_items_id_fk' => $ndrItem->ndr_items_id,
                            'po_items_spec_id_fk' => $poSpec->po_items_spec_id,
                            'ndr_spec_description' => $poSpec->po_spec_description,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery Attachments generated successfully.',
                'redirect' => route('show.delivery.attachment', ['po_id' => $po->po_id])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
