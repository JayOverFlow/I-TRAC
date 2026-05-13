<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PrParent;
use App\Models\PoParent;

class ProcureController extends Controller
{
    public function showProcure() {
        $user = Auth::user();
        $userRole = $user->roles()->first();
        
        // 1. Fetch Retrieved Purchase Requests
        $retrievedPrs = PrParent::where('retrieved_by', $user->user_id)->get();

        // 2. Fetch Created Purchase Orders
        $pos = PoParent::with('purchaseRequest')->where('saved_by_user_id_fk', $user->user_id)->get();

        // 3. Check if user gen_role is Procurement 
        // if ($userRole != 'Procurement') {
        //     abort(404, 'Unauthorized access for your account');
        // }

        return view('procurement.pages.procurement-procure', compact('user', 'retrievedPrs', 'pos'));

        // 4. Redirect user to Procure Page with render PRs and POs
    }
}
