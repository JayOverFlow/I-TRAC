<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mr;
use Illuminate\Support\Facades\Auth;

class MrApiController extends Controller
{
    public function assignItems(Request $request)
    {
        $request->validate([
            'mr_qr_code' => 'required|string',
        ]);

        $qrCode = $request->mr_qr_code;
        $user = Auth::user();

        // 1. Check if the QR code exists in the database
        $itemsExist = Mr::where('mr_qr_code', $qrCode)->exists();
        if (!$itemsExist) {
            return response()->json([
                'status' => 'error',
                'message' => 'No items found matching the scanned QR code.'
            ], 404);
        }

        // 2. Check if any items are already assigned to someone else
        $assignedToOthers = Mr::where('mr_qr_code', $qrCode)
            ->where('is_assigned', 1)
            ->where('assigned_to', '!=', $user->user_id)
            ->with('assignedUser')
            ->get();

        if ($assignedToOthers->isNotEmpty()) {
            $otherNames = $assignedToOthers->map(function ($item) {
                return $item->assignedUser
                    ? trim($item->assignedUser->user_firstname . ' ' . $item->assignedUser->user_lastname)
                    : 'another user';
            })->unique()->implode(', ');

            return response()->json([
                'status' => 'error',
                'message' => "These items have already been claimed by: {$otherNames}."
            ], 400);
        }

        // 3. Find items that are currently pending assignment (is_assigned = 0)
        $pendingItems = Mr::where('mr_qr_code', $qrCode)
            ->where('is_assigned', 0)
            ->get();

        if ($pendingItems->isEmpty()) {
            // Check if they are already assigned to the current user
            $alreadyAssignedToMe = Mr::where('mr_qr_code', $qrCode)
                ->where('is_assigned', 1)
                ->where('assigned_to', $user->user_id)
                ->get();

            if ($alreadyAssignedToMe->isNotEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'You have already claimed these items.',
                    'items' => $alreadyAssignedToMe
                ], 200);
            }
        }

        // 4. Update the pending items
        Mr::where('mr_qr_code', $qrCode)
            ->where('is_assigned', 0)
            ->update([
                'is_assigned' => 1,
                'assigned_to' => $user->user_id,
                'date_scanned' => now(),
            ]);

        // Fetch the updated items to return to the mobile app
        $assignedItems = Mr::where('mr_qr_code', $qrCode)
            ->where('assigned_to', $user->user_id)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Items successfully claimed and recorded to your account.',
            'items' => $assignedItems
        ], 200);
    }

    public function getUserItems(Request $request)
    {
        $user = Auth::user();

        $items = Mr::where('assigned_to', $user->user_id)
            ->where('is_assigned', 1)
            ->with('poItem')
            ->get()
            ->map(function ($item) {
                $dbCategory = $item->poItem ? $item->poItem->po_items_category : null;
                $category = 'All';
                if ($dbCategory === 'Supply and Materials') {
                    $category = 'Supplies';
                } elseif ($dbCategory === 'Semi-Expendable') {
                    $category = 'Semi-Expendable';
                } elseif ($dbCategory === 'Equipment') {
                    $category = 'Equipment';
                }

                return [
                    'mr_id' => $item->mr_id,
                    'item_name' => $item->item_name,
                    'specification' => $item->specification,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'stock' => $item->stock,
                    'location' => $item->location ?? 'Unknown Location',
                    'item_image' => $item->item_image,
                    'date_scanned' => $item->date_scanned,
                    'category' => $category,
                ];
            });

        return response()->json([
            'status' => 'success',
            'items' => $items
        ]);
    }

    public function updateItemImage(Request $request)
{
    try {
        $request->validate([
            'item_id' => 'required|integer',
            'item_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Find using mr_id since that is your primary key
        $item = \App\Models\Mr::where('mr_id', $request->item_id)->first();

        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Item not found in database.'], 404);
        }

        if ($request->hasFile('item_image')) {
            $file = $request->file('item_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Save to public/img/items/
            $file->move(public_path('img/items'), $filename);
            
            // Update column
            $item->item_image = 'img/items/' . $filename;
            $item->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Item image updated successfully!',
                'image_url' => asset($item->item_image)
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'No image file detected.'], 400);

    } catch (\Exception $e) {
        // This will prevent the "infinite loading" by returning the error message
        return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
    }
}
}
