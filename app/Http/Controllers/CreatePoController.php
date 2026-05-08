<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CreatePoController extends Controller
{
    public function showCreatePo() {
        return view('procurement/pages/procurement-create-po');
    }
}
