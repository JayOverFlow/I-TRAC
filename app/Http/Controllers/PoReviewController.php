<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PoReviewController extends Controller
{
    public function showPoReview() { // Pass the po_id
        // 1. Fetch retrieved Purchase Order (po_tbl, po_items_tbl, and po_items_specs_tbl) by user
        // 2. Return suppl-po-review

        return view('supply.pages.supply-po-review');
    }
}
