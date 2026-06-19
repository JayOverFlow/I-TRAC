<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

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
                    'location' => ($item->building && $item->room_no)
                        ? "{$item->building} - {$item->room_no}"
                        : ($item->building ?? $item->room_no ?? 'Unknown Location'),
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
            ]);

            $item = Mr::where('mr_id', $request->item_id)->first();

            if (!$item) {
                return response()->json(['status' => 'error', 'message' => 'Item not found in database.'], 404);
            }

            // 1. Process Existing Images (reordering)
            $finalImages = [];
            $existing = $request->input('existing_images');
            if ($existing) {
                $finalImages = is_array($existing) ? $existing : [$existing];
            }

            // 2. Process ALL New Uploads in one go
            if ($request->hasFile('item_image')) {
                $uploaded = $request->file('item_image');

                // Handle both single file and array of files
                $files = is_array($uploaded) ? $uploaded : [$uploaded];

                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                        $file->move(public_path('img/items'), $filename);
                        $finalImages[] = 'img/items/' . $filename;
                    }
                }
            }

            // 3. Save definitive state
            $item->item_image = empty($finalImages) ? null : json_encode(array_values($finalImages));
            $item->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Images synced successfully!',
                'all_images' => $item->item_image
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteItemImage(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|integer',
                'image_path' => 'required|string',
            ]);

            $item = Mr::where('mr_id', $request->item_id)->first();

            if (!$item) {
                return response()->json(['status' => 'error', 'message' => 'Item not found.'], 404);
            }

            if (!$item->item_image) {
                return response()->json(['status' => 'error', 'message' => 'No images found for this item.'], 400);
            }

            $existingImages = [];
            $decoded = json_decode($item->item_image, true);
            if (is_array($decoded)) {
                $existingImages = $decoded;
            } else {
                $existingImages = [$item->item_image];
            }

            $targetPath = $request->image_path;

            if (($key = array_search($targetPath, $existingImages)) !== false) {
                unset($existingImages[$key]);

                $fullPath = public_path($targetPath);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }

                $item->item_image = empty($existingImages) ? null : json_encode(array_values($existingImages));
                $item->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Image deleted successfully.',
                    'all_images' => $item->item_image
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'Image path not found in item record.'], 404);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function updateItemLocation(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|integer',
                'building' => 'nullable|string|max:255',
                'room_no' => 'nullable|string|max:50',
            ]);

            $item = Mr::where('mr_id', $request->item_id)->first();

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
