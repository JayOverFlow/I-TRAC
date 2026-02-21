<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssignPrController extends Controller
{
    public function showAssignPr ($app_id) {
        // Fetch the APP data using app_id
        $app_data = null;

        return view('head/pages/head-assign-pr', compact('app_data'));
    }
}
