<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PoParent;
use Illuminate\Support\Facades\Auth;

class CreatePoController extends Controller
{
    public function showCreatePo($po_id) {
        $po = PoParent::findOrFail($po_id);
        return view('procurement/pages/procurement-create-po', compact('po'));
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
        ]);

        return redirect()->route('show.create.po', ['po_id' => $po->po_id])
            ->with('success', 'Purchase Order created successfully.');
    }
}
