<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MrController extends Controller
{
    public function showMr() {
        // Get the authenticated user
        $user = Auth::user();

        // Resolve active role dynamically based on active session context
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();
        $userRole = $activeRole?->gen_role;

        // Get necessary data to render
        $data = \App\Models\Mr::with('images')->where('assigned_to', $user->user_id)->get();

        // Redirect user based on role
        return match ($userRole) {
            'Head'             => view('head/pages/head-mr', compact('data')),
            null, 'Unassigned' => view('unassigned/pages/unassigned-mr', compact('data')), // Unassigned (No role) users
            'Procurement'      => view('procurement/pages/procurement-mr', compact('data')),
            'Supply'           => view('supply/pages/supply-mr', compact('data')),
            default            => abort(403),
        };
    }

    public function updateLocation(Request $request)
    {
        try {
            $request->validate([
                'mr_id' => 'required|integer',
                'building' => 'nullable|string|max:255',
                'room_no' => 'nullable|string|max:50',
            ]);

            $item = \App\Models\Mr::where('mr_id', $request->mr_id)->first();

            if (!$item) {
                return response()->json(['status' => 'error', 'message' => 'Item not found.'], 404);
            }

            $item->building = $request->building;
            $item->room_no = $request->room_no;
            $item->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Location updated successfully!',
                'item' => [
                    'mr_id' => $item->mr_id,
                    'building' => $item->building,
                    'room_no' => $item->room_no,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}
