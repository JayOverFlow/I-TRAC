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
        $pr = PrParent::with(['prItems.prSpecs', 'prItems.appItem', 'department', 'requestor'])
            ->findOrFail($pr_id);

        // Category display order
        $categoryOrder = [
            'consumable'    => 'Consumables',
            'equipment'     => 'Equipment',
            'equipment_50k' => 'Equipment (50k & ↑)',
        ];

        // Group items by project title, then by category within each project
        $groupedItems = $pr->prItems
            ->groupBy(fn($item) => $item->appItem->app_item_proj_title ?? 'Untitled Project')
            ->map(function ($items) use ($categoryOrder) {
                // Group by category within this project, ordered by $categoryOrder
                $byCategory = $items->groupBy('pr_items_category');
                $sorted = collect();
                foreach ($categoryOrder as $key => $label) {
                    if ($byCategory->has($key)) {
                        $sorted->put($key, $byCategory->get($key));
                    }
                }
                return $sorted;
            });

        return view('procurement.pages.procurement-pr-preview', compact('pr', 'groupedItems', 'categoryOrder'));
    }
}
