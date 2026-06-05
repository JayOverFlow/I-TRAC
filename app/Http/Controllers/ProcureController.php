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
        // Resolve active role dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;
        
        if ($userRole === 'Procurement') {
            // Fetch Retrieved Purchase Requests and Created Purchase Orders
            $retrievedPrs = PrParent::where('retrieved_by', $user->user_id)->get();
            $pos = PoParent::with('purchaseRequest.app')->where('saved_by_user_id_fk', $user->user_id)->get();
            
            return view('procurement.pages.procurement-procure', compact('user', 'retrievedPrs', 'pos'));
        }

        if ($userRole === 'Supply') {
            // Fetch only Retrieved Purchase Orders
            $pos = PoParent::with('purchaseRequest.app')->where('retrieved_by', $user->user_id)->get();
            
            return view('supply.pages.supply-procure', compact('user', 'pos'));
        }

        abort(403, 'Unauthorized access for your account');
    }

    public function retrievePr(Request $request)
    {
        $request->validate([
            'pr_unique_code' => ['required', 'string', 'max:50'],
        ]);

        $prCode = trim($request->pr_unique_code);

        // Self-healing: If user submitted PR202601001 instead of PR-202601-001
        if (preg_match('/^PR\d{9}$/i', $prCode)) {
            $prCode = 'PR-' . substr($prCode, 2, 6) . '-' . substr($prCode, 8);
        }

        // Find the PR with the given unique code that is 'Exported'
        $pr = \App\Models\PrParent::where('pr_unique_code', $prCode)
            ->where('pr_status', 'Exported')
            ->first();

        if (!$pr) {
            return redirect(url()->previous() . '#animated-underline-purchase-request')->with('error', "Purchase Request '$prCode' not found or not yet exported.");
        }

        // Check if it's already retrieved
        if ($pr->retrieved_by) {
            if ($pr->retrieved_by === Auth::id()) {
                return redirect(url()->previous() . '#animated-underline-purchase-request')->with('success', "Purchase Request '$prCode' is already in your list.");
            }
            return redirect(url()->previous() . '#animated-underline-purchase-request')->with('error', "Purchase Request '$prCode' has already been retrieved by another procurement user.");
        }

        // Assign retrieval to current user
        $pr->update(['retrieved_by' => Auth::id()]);

        return redirect(route('show.procure'))->with('success', "Purchase Request '$prCode' successfully retrieved.");
    }

    public function retrievePo(Request $request) {
        $request->validate([
            'po_unique_code' => ['required', 'string', 'max:50'],
        ]);

        $poCode = trim($request->po_unique_code);

        // Self-healing: If user submitted PO202601001001 instead of PO-202601001-001
        if (preg_match('/^PO\d{12}$/i', $poCode)) {
            $poCode = 'PO-' . substr($poCode, 2, 9) . '-' . substr($poCode, 11);
        }

        $po = PoParent::where('po_unique_code', $poCode)
            ->where('po_status', 'Done')
            ->first();

        if (!$po) {
            return redirect(url()->previous())->with('error', "Purchase Order '$poCode' not found or not yet done.");
        }

        if ($po->retrieved_by) {
            if ($po->retrieved_by === Auth::id()) {
                return redirect(url()->previous())->with('success', "Purchase Order '$poCode' is already in your list.");
            }
            return redirect(url()->previous())->with('error', "Purchase Order '$poCode' has already been retrieved by another user.");
        }

        $po->update(['retrieved_by' => Auth::id()]);

        return redirect(url()->previous())->with('success', "Purchase Order '$poCode' successfully retrieved.");
    }
}
