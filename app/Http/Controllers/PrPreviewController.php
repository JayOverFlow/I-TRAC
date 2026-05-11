<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrParent;
use Illuminate\Support\Facades\Auth;

class PrPreviewController extends Controller
{
    public function showPrPreview($pr_id)
    {
        // Load the PR with items (eager load specs and appItem for project title)
        $pr = PrParent::with(['prItems.prSpecs', 'prItems.appItem', 'department', 'requestor', 'purchaseOrders'])
            ->findOrFail($pr_id);

        // Category display order
        $categoryOrder = [
            'supply_and_materials' => 'Supply and Materials',
            'semi-expendable'      => 'Semi-expendable',
            'equipment'            => 'Equipment',
        ];

        // Group items by project title, then by category within each project
        $groupedItems = $pr->prItems
            ->groupBy(fn($item) => $item->appItem?->app_item_proj_title ?? 'Untitled Project')
            ->map(function ($items) use ($categoryOrder) {
                // Group by category within this project, ordered by $categoryOrder
                $byCategory = $items->groupBy('pr_items_category');
                $sorted = collect();
                
                // Add categories in specified order
                foreach ($categoryOrder as $key => $label) {
                    if ($byCategory->has($key)) {
                        $sorted->put($key, $byCategory->get($key));
                        $byCategory->forget($key);
                    }
                }
                
                // Include any remaining items (if they don't match or are null)
                if ($byCategory->isNotEmpty()) {
                    foreach ($byCategory as $key => $catItems) {
                        $newKey = $key ?: 'other';
                        if ($sorted->has($newKey)) {
                            $sorted->put($newKey, $sorted->get($newKey)->concat($catItems));
                        } else {
                            $sorted->put($newKey, $catItems);
                        }
                    }
                }
                
                return $sorted;
            });

        return view('procurement.pages.procurement-pr-preview', compact('pr', 'groupedItems', 'categoryOrder'));
    }
}
