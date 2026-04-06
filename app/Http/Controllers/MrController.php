<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class MrController extends Controller
{
    public function showMr() {
        // Get authenticaton user data
        $user = Auth::user();

        // Get neccessary datas
        $data = null;
        
        return view('general-pages/mr', compact('data'));
    }
}
