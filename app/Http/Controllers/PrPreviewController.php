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

        // Group items by project title
        $groupedItems = $pr->prItems->groupBy(fn($item) => $item->appItem?->app_item_proj_title ?? 'Untitled Project');

        return view('procurement.pages.procurement-pr-preview', compact('pr', 'groupedItems'));
    }
}
