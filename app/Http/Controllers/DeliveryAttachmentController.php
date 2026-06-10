<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoParent;

class DeliveryAttachmentController extends Controller
{
    public function showDeliveryAttachment($po_id)
    {
        $po = PoParent::with([
            'iarReports.iarItems.iarSpecs',
            'risSlips.risItems.risSpecs',
            'risSlips.requester',
            'risSlips.receiver',
            'rsmiReports.rsmiItems.rsmiSpecs',
            'rsmiReports.user',
            'icsSlips.icsItems.icsSpecs',
            'icsSlips.receiver.departments',
            'icsSlips.giver',
            'rspiReports.rspiItems.rspiSpecs',
            'rspiReports.user',
            'parReceipts.parItems.parSpecs',
            'parReceipts.receiver.departments',
            'parReceipts.issuer',
            'ndrReports.ndrItems.ndrSpecs',
            'ndrReports.reporter'
        ])->findOrFail($po_id);

        // Redirect to PO Review if no delivery attachments have been generated
        if (!$po->iarReports()->exists() && 
            !$po->risSlips()->exists() && 
            !$po->icsSlips()->exists() && 
            !$po->parReceipts()->exists() && 
            !$po->rsmiReports()->exists() && 
            !$po->rspiReports()->exists() &&
            !$po->ndrReports()->exists()) {
            return redirect()->route('show.po.review', ['po_id' => $po_id]);
        }

        $headPropertySupply = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('roles_tbl.role_id', 10);
        })->first();

        $users = \App\Models\User::all();

        return view('supply.pages.supply-delivery-attachment', compact('po', 'headPropertySupply', 'users'));
    }
}
