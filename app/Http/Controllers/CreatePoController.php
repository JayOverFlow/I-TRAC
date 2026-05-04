<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrParent;
use App\Models\PoParent;
use App\Models\PoItem;
use App\Models\PoSpec;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatePoController extends Controller
{
    public function showCreatePo($pr_id)
    {
        $user = Auth::user();
        
        // Load Approved PR with items and specs
        $pr = PrParent::with(['prItems.prSpecs', 'prItems.appItem', 'department', 'requestor'])
            ->where('pr_status', 'Approved')
            ->findOrFail($pr_id);

        // Check if a PO draft already exists for this PR
        $po = PoParent::with(['poItems.poSpecs'])->where('po_pr_id_fk', $pr_id)->first();
        
        $savedItemsGrouped = collect();
        if ($po) {
            // Group saved items by app_item_id or pr_item_id to handle rendering
            $savedItemsGrouped = $po->poItems->groupBy('po_app_item_id_fk');
        } else {
            // If no PO yet, we prepare to render based on PR items
            $savedItemsGrouped = $pr->prItems->groupBy('pr_app_item_id_fk');
        }

        // Group unique APP items by project title for rendering
        $groupedItems = $pr->prItems->map(fn($item) => $item->appItem)
            ->filter()
            ->unique('app_item_id')
            ->groupBy('app_item_proj_title');

        // Normalize items (Standardize PrItem/PoItem data for the view)
        $savedItemsGrouped = $savedItemsGrouped->map(function ($items) {
            return $items->map(function ($item) {
                $isPo = $item instanceof \App\Models\PoItem;
                return (object)[
                    'unit'          => $isPo ? $item->po_items_unit : $item->pr_items_unit,
                    'description'   => $isPo ? $item->po_items_descrip : $item->pr_items_descrip,
                    'quantity'      => $isPo ? $item->po_items_quantity : $item->pr_items_quantity,
                    'cost'          => $isPo ? $item->po_items_cost : $item->pr_items_cost,
                    'category'      => $isPo ? ($item->prItem?->pr_items_category ?? 'consumable') : $item->pr_items_category,
                    'specification' => $isPo
                        ? ($item->poSpecs->first()?->po_spec_description ?? '')
                        : ($item->prSpecs->first()?->pr_spec_spec ?? '')
                ];
            });
        });

        return view('procurement.pages.procurement-create-po', compact('pr', 'po', 'groupedItems', 'savedItemsGrouped'));
    }

    public function savePo(Request $request, $pr_id)
    {
        $user = Auth::user();
        $pr = PrParent::findOrFail($pr_id);
        $status = $request->input('status', 'Draft');

        try {
            DB::transaction(function () use ($request, $user, $pr, $status) {
                // Find or create PO header
                $po = PoParent::where('po_pr_id_fk', $pr->pr_id)->first();

                if (!$po) {
                    $lastPo = PoParent::orderBy('po_id', 'desc')->first();
                    $nextNum = $lastPo ? ($lastPo->po_id + 1) : 1;
                    $uniqueCode = 'PO' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                    $po = PoParent::create([
                        'po_pr_id_fk' => $pr->pr_id,
                        'saved_by_user_id_fk' => $user->user_id,
                        'po_unique_code' => $uniqueCode,
                        'po_date' => now()->toDateString(),
                    ]);
                }

                // Clean up old items for replacement
                $po->poItems()->delete();

                // Save items
                foreach ($request->input('items', []) as $row) {
                    $appItemId = $row['app_item_id'] ?? null;
                    if (!$appItemId || (empty($row['description']) && empty($row['quantity']))) {
                        continue;
                    }

                    $qty = (int) ($row['quantity'] ?? 0);
                    $cost = (float) ($row['cost'] ?? 0);
                    $amount = $qty * $cost;

                    $poItem = PoItem::create([
                        'po_id_fk' => $po->po_id,
                        'po_app_item_id_fk' => $appItemId,
                        'po_items_descrip' => $row['description'] ?? null,
                        'po_items_unit' => $row['unit'] ?? null,
                        'po_items_quantity' => $qty,
                        'po_items_cost' => $cost,
                        'po_items_amount' => $amount,
                        'po_items_total' => $amount,
                    ]);

                    if (!empty($row['specification'])) {
                        PoSpec::create([
                            'po_items_id_fk' => $poItem->po_items_id,
                            'po_spec_description' => $row['specification'],
                        ]);
                    }
                }
            });

            return redirect()->route('show.create.po', $pr->pr_id)
                ->with('success', 'Purchase Order ' . ($status === 'Draft' ? 'saved as draft.' : 'created successfully.'));
        } catch (\Exception $e) {
            Log::error('PO Save Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to save Purchase Order.');
        }
    }
}
