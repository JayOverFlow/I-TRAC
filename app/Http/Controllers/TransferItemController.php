<?php

namespace App\Http\Controllers;

use App\Models\Mr;
use App\Models\Ics;
use App\Models\IcsItem;
use App\Models\IcsItemSpec;
use App\Models\Par;
use App\Models\ParItem;
use App\Models\ParItemSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransferItemController extends Controller
{
    /**
     * Initializes transfer from supply inventory.
     */
    public function transferItem($mrId)
    {
        try {
            $mr = Mr::findOrFail($mrId);
            $poId = $mr->poItem?->po_id_fk;
            if (!$poId) {
                return response()->json(['success' => false, 'message' => 'No Purchase Order found for this item.'], 400);
            }

            if ($mr->category === 'Semi-Expendable') {
                $orig = Ics::with(['icsItems.icsSpecs.poSpec'])->where('po_id_fk', $poId)->where('is_transfer', 0)->first();
                if (!$orig) {
                    return response()->json(['success' => false, 'message' => 'Original ICS not found for this PO.'], 404);
                }

                $newDoc = DB::transaction(function () use ($poId, $orig, $mr) {
                    $doc = Ics::create([
                        'po_id_fk' => $poId,
                        'ics_fund_cluster' => $orig->ics_fund_cluster,
                        'ics_po_no' => $orig->ics_po_no,
                        'ics_no' => $orig->ics_no,
                        'ics_code_no' => $orig->ics_code_no,
                        'ics_received_from' => $orig->ics_received_from,
                        'ics_received_from_pos' => $orig->ics_received_from_pos,
                        'ics_received_from_date' => $orig->ics_received_from_date,
                        'ics_received_by' => null,
                        'ics_received_by_pos' => 'Faculty / Staff',
                        'ics_received_by_date' => null,
                        'is_transfer' => 1,
                        'mr_id_fk' => $mr->mr_id,
                    ]);

                    foreach ($orig->icsItems as $item) {
                        $isTarget = false;
                        if ($item->icsSpecs->isNotEmpty()) {
                            foreach ($item->icsSpecs as $spec) {
                                if ($spec->poSpec && $spec->poSpec->po_items_id_fk === $mr->po_item_id_fk) {
                                    $isTarget = true;
                                    break;
                                }
                            }
                        }
                        if (!$isTarget) {
                            if ($item->ics_items_descrip === $mr->item_name && $item->ics_inventory_item_no === $mr->stock) {
                                $isTarget = true;
                            }
                        }

                        if (!$isTarget) {
                            continue;
                        }

                        $newItem = IcsItem::create([
                            'ics_id_fk' => $doc->ics_id,
                            'ics_quantity' => $item->ics_quantity,
                            'ics_unit' => $item->ics_unit,
                            'ics_unit_cost' => $item->ics_unit_cost,
                            'ics_total_cost' => $item->ics_total_cost,
                            'ics_items_descrip' => $item->ics_items_descrip,
                            'ics_inventory_item_no' => $item->ics_inventory_item_no,
                            'ics_estimated_useful_life' => $item->ics_estimated_useful_life,
                        ]);

                        foreach ($item->icsSpecs as $spec) {
                            IcsItemSpec::create([
                                'ics_items_id_fk' => $newItem->ics_items_id,
                                'po_items_spec_id_fk' => $spec->po_items_spec_id_fk,
                                'ics_spec_description' => $spec->ics_spec_description,
                            ]);
                        }
                    }
                    return $doc;
                });

                session()->flash('active_document', 'doc-ics-' . $newDoc->ics_id);
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('show.delivery.attachment', ['po_id' => $poId]),
                ]);
            } elseif ($mr->category === 'Equipment') {
                $orig = Par::with(['parItems.parSpecs'])->where('po_id_fk', $poId)->where('is_transfer', 0)->first();
                if (!$orig) {
                    return response()->json(['success' => false, 'message' => 'Original PAR not found for this PO.'], 404);
                }

                $newDoc = DB::transaction(function () use ($poId, $orig, $mr) {
                    $doc = Par::create([
                        'po_id_fk' => $poId,
                        'par_fund_cluster' => $orig->par_fund_cluster,
                        'par_po_no' => $orig->par_po_no,
                        'par_no' => $orig->par_no,
                        'par_code' => $orig->par_code,
                        'par_issued_by' => $orig->par_issued_by,
                        'par_issued_by_pos' => $orig->par_issued_by_pos,
                        'par_issued_by_date' => $orig->par_issued_by_date,
                        'par_received_by' => null,
                        'par_received_by_pos' => 'Faculty / Staff',
                        'par_received_by_date' => null,
                        'is_transfer' => 1,
                        'mr_id_fk' => $mr->mr_id,
                    ]);

                    foreach ($orig->parItems as $item) {
                        if ($item->par_po_items_id_fk !== $mr->po_item_id_fk) {
                            continue;
                        }

                        $newItem = ParItem::create([
                            'par_id_fk' => $doc->par_id,
                            'par_po_items_id_fk' => $item->par_po_items_id_fk,
                            'par_quantity' => $item->par_quantity,
                            'par_unit' => $item->par_unit,
                            'par_items_descrip' => $item->par_items_descrip,
                            'par_property_no' => $item->par_property_no,
                            'par_date_acquired' => $item->par_date_acquired,
                            'par_amount' => $item->par_amount,
                        ]);

                        foreach ($item->parSpecs as $spec) {
                            ParItemSpec::create([
                                'par_items_id_fk' => $newItem->par_items_id,
                                'po_items_spec_id_fk' => $spec->po_items_spec_id_fk,
                                'par_spec_description' => $spec->par_spec_description,
                            ]);
                        }
                    }
                    return $doc;
                });

                session()->flash('active_document', 'doc-par-' . $newDoc->par_id);
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('show.delivery.attachment', ['po_id' => $poId]),
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Invalid category for transfer.'], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Submits transfer transaction for ICS.
     */
    public function transferIcsSubmit($ics_id, Request $request)
    {
        $rules = [
            'ics_received_by' => 'required|exists:users,user_id',
            'ics_received_by_date' => 'required|date',
        ];
        $messages = [
            'ics_received_by.required' => 'Recipient is required.',
            'ics_received_by_date.required' => 'Transfer date is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

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
                ->with('active_document', 'doc-ics-' . $ics_id);
        }

        try {
            $ics = Ics::findOrFail($ics_id);
            DB::transaction(function () use ($ics, $request) {
                $ics->update([
                    'ics_received_by' => $request->ics_received_by,
                    'ics_received_by_date' => $request->ics_received_by_date,
                ]);

                if ($ics->mr_id_fk) {
                    Mr::where('mr_id', $ics->mr_id_fk)->update([
                        'assigned_to' => $request->ics_received_by,
                        'is_assigned' => 0,
                    ]);
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                session()->flash('active_document', 'doc-ics-' . $ics_id);
                return response()->json([
                    'success' => true,
                    'message' => 'Item transferred successfully via ICS.',
                    'active_document' => 'doc-ics-' . $ics_id,
                    'redirect_url' => route('show.delivery.attachment', $ics->po_id_fk)
                ]);
            }

            return redirect()->back()
                ->with('success', 'Item transferred successfully via ICS.')
                ->with('active_document', 'doc-ics-' . $ics_id);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to transfer item: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to transfer item: ' . $e->getMessage())
                ->with('active_document', 'doc-ics-' . $ics_id);
        }
    }

    /**
     * Submits transfer transaction for PAR.
     */
    public function transferParSubmit($par_id, Request $request)
    {
        $rules = [
            'par_received_by' => 'required|exists:users,user_id',
            'par_received_by_date' => 'required|date',
        ];
        $messages = [
            'par_received_by.required' => 'Recipient is required.',
            'par_received_by_date.required' => 'Transfer date is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

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
                ->with('active_document', 'doc-par-' . $par_id);
        }

        try {
            $par = Par::findOrFail($par_id);
            DB::transaction(function () use ($par, $request) {
                $par->update([
                    'par_received_by' => $request->par_received_by,
                    'par_received_by_date' => $request->par_received_by_date,
                ]);

                if ($par->mr_id_fk) {
                    Mr::where('mr_id', $par->mr_id_fk)->update([
                        'assigned_to' => $request->par_received_by,
                        'is_assigned' => 0,
                    ]);
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                session()->flash('active_document', 'doc-par-' . $par_id);
                return response()->json([
                    'success' => true,
                    'message' => 'Item transferred successfully via PAR.',
                    'active_document' => 'doc-par-' . $par_id,
                    'redirect_url' => route('show.delivery.attachment', $par->po_id_fk)
                ]);
            }

            return redirect()->back()
                ->with('success', 'Item transferred successfully via PAR.')
                ->with('active_document', 'doc-par-' . $par_id);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to transfer item: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to transfer item: ' . $e->getMessage())
                ->with('active_document', 'doc-par-' . $par_id);
        }
    }
}
