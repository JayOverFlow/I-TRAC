<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CreateAppController extends Controller
{
    public function showCreateApp() {
        return view('head/pages/head-create-app');
    }

    public function createApp(Request $request) {
        // Logic
    }
}
