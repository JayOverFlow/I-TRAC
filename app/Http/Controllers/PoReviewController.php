<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoParent;

class PoReviewController extends Controller
{
    public function showPoReview($po_id) { 
        // 1. Fetch retrieved Purchase Order (po_tbl, po_items_tbl, and po_items_specs_tbl) by user
        $po = PoParent::with(['poItems.poSpecs', 'savedBy'])->findOrFail($po_id);
        
        $breadcrumbs = [
            ['title' => 'Procurement', 'url' => route('show.procure')],
            ['title' => 'PO Review', 'url' => '']
        ];
        
        // 2. Return supply-po-review
        return view('supply.pages.supply-po-review', compact('po', 'breadcrumbs'));
    }
}
