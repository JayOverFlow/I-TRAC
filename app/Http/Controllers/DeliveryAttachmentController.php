<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeliveryAttachmentController extends Controller
{
    public function showDeliveryAttachment() { // Pass po_id as parameter   
        return view('supply.pages.supply-delivery-attachment');
    }
}
