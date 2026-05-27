<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeliveryAttachmentController extends Controller
{
    public function showDeliveryAttachment() { // Pass po_id as parameter   
        $breadcrumbs = [
            ['title' => 'Procurement', 'url' => route('show.procure')],
            ['title' => 'Delivery Attachment', 'url' => '']
        ];
        return view('supply.pages.supply-delivery-attachment', compact('breadcrumbs'));
    }
}
