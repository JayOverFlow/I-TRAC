<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PoParent;
use App\Models\PoItem;
use App\Models\PoSpec;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePoController extends Controller
{
    public function showCreatePo($po_id) {
        $po = PoParent::with(['poItems.poSpecs'])->findOrFail($po_id);

        // Group items by category for structured rendering in Blade
        $supplyItems = $po->poItems->where('po_items_category', 'supply_and_materials');
        $semiItems = $po->poItems->where('po_items_category', 'semi-expendable');
        $equipItems = $po->poItems->where('po_items_category', 'equipment');
        $otherItems = $po->poItems->whereNotIn('po_items_category', ['supply_and_materials', 'semi-expendable', 'equipment']);

        // Ensure the staging area ('Add Items' card) always has at least one row for interactivity
        if ($otherItems->isEmpty()) {
            $otherItems = collect([new PoItem()]);
        }

        return view('procurement/pages/procurement-create-po', compact('po', 'supplyItems', 'semiItems', 'equipItems', 'otherItems'));
    }

    public function createPo(Request $request, $pr_id) {
        $request->validate([
            'po_title' => 'required|string|max:45',
        ]);

        $user = Auth::user();

        // Generate incrementing unique code (PO0000 format)
        $lastPo = PoParent::orderBy('po_id', 'desc')->first();
        $nextNum = $lastPo ? ($lastPo->po_id + 1) : 1;
        $uniqueCode = 'PO' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

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

    public function updatePo(Request $request, $po_id) {
        $po = PoParent::findOrFail($po_id);

        DB::beginTransaction();
        try {
            // Update PO Parent
            $po->update([
                'po_supplier' => $request->po_supplier,
                'po_address' => $request->po_address,
                'po_tele' => $request->po_tele,
                'po_tin' => $request->po_tin,
                'po_place_delivery' => $request->po_place_delivery,
                'po_date_delivery' => $request->po_date_delivery,
                'po_no' => $request->po_no,
                'po_date' => $request->po_date,
                'po_mode' => $request->po_mode,
                'po_tuptin' => $request->po_tuptin,
                'po_delivery_term' => $request->po_delivery_term,
                'po_payment_term' => $request->po_payment_term,
                'po_status' => $request->po_status ?? 'Draft',
                'po_total_amount' => $request->po_total_amount ?? 0,
            ]);

            // Sync Items: Delete and Recreate for simplicity (Beginner Friendly)
            $po->poItems()->delete();

            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    // Ignore and do not store/save item/row that do not have category selected
                    if (empty($itemData['category'])) {
                        continue;
                    }

                    $item = $po->poItems()->create([
                        'po_items_stockno' => $itemData['stock'] ?? null,
                        'po_items_unit' => $itemData['unit'] ?? null,
                        'po_items_descrip' => $itemData['description'] ?? null,
                        'po_items_quantity' => $itemData['quantity'] ?? 0,
                        'po_items_cost' => $itemData['cost'] ?? 0,
                        'po_items_total' => ($itemData['quantity'] ?? 0) * ($itemData['cost'] ?? 0),
                        'po_items_category' => $itemData['category'],
                    ]);

                    // Add Specification if present
                    if (!empty($itemData['specification'])) {
                        $item->poSpecs()->create([
                            'po_spec_description' => $itemData['specification'],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('show.create.po', ['po_id' => $po->po_id])
                ->with('success', 'Purchase Order updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating Purchase Order: ' . $e->getMessage());
        }
    }
}
