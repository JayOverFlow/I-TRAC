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

        $payload = $request->mr_qr_code;
        $user = Auth::user();

        // 1. Parse the payload (e.g., RIS-11, PAR-5)
        if (!preg_match('/^(RIS|PAR)-(\d+)$/', $payload, $matches)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid QR code format. Please scan a valid RIS or PAR form.'
            ], 400);
        }

        $type = $matches[1];
        $id = $matches[2];

        // 2. Resolve the form items to MR records and validate ownership
        if ($type === 'RIS') {
            $form = \App\Models\Ris::find($id);
            if (!$form) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'RIS form not found.'
                ], 404);
            }
            if ($form->ris_received_by != $user->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not the designated receiver for this RIS.'
                ], 403);
            }
            $poItemIds = \App\Models\RisItem::where('ris_id_fk', $id)->pluck('ris_po_items_id_fk');
        } else {
            $form = \App\Models\Par::find($id);
            if (!$form) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'PAR form not found.'
                ], 404);
            }
            if ($form->par_received_by != $user->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not the designated receiver for this PAR.'
                ], 403);
            }
            $poItemIds = \App\Models\ParItem::where('par_id_fk', $id)->pluck('par_po_items_id_fk');
        }

        if ($poItemIds->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "No items found for this {$type} form."
            ], 404);
        }

        $itemsToAssign = Mr::whereIn('po_item_id_fk', $poItemIds)->get();

        if ($itemsToAssign->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "No trackable inventory items found for this {$type} form."
            ], 404);
        }

        // 3. Check for existing assignments
        $assignedToOthers = $itemsToAssign->filter(function ($item) use ($user) {
            return $item->is_assigned && $item->assigned_to != $user->user_id;
        });

        if ($assignedToOthers->isNotEmpty()) {
            $otherNames = $assignedToOthers->map(function ($item) {
                return $item->assignedUser
                    ? trim($item->assignedUser->user_firstname . ' ' . $item->assignedUser->user_lastname)
                    : 'another user';
            })->unique()->implode(', ');

            return response()->json([
                'status' => 'error',
                'message' => "Some items in this form have already been claimed by: {$otherNames}."
            ], 400);
        }

        // 4. Find pending items
        $pendingItems = $itemsToAssign->where('is_assigned', 0);

        if ($pendingItems->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'You have already claimed all items in this form.',
                'items' => $itemsToAssign
            ], 200);
        }

        // 5. Batch update
        $pendingIds = $pendingItems->pluck('mr_id');
        Mr::whereIn('mr_id', $pendingIds)->update([
            'is_assigned' => 1,
            'assigned_to' => $user->user_id,
            'date_scanned' => now(),
        ]);

        // Fetch fully updated items for the response
        $finalItems = Mr::whereIn('mr_id', $itemsToAssign->pluck('mr_id'))->get();

        // 6. Update PR scanned_at if all RIS/PAR DAs are fully scanned
        $po = $form->purchaseOrder;
        if ($po && $po->purchaseRequest) {
            $pr = $po->purchaseRequest;
            $pr->load('purchaseOrders.risSlips.risItems', 'purchaseOrders.parReceipts.parItems');

            $allDAsScanned = true;
            $hasAnyDa = false;

            foreach ($pr->purchaseOrders as $p) {
                // Check RIS forms
                foreach ($p->risSlips as $ris) {
                    $risPoItemIds = $ris->risItems->pluck('ris_po_items_id_fk')->filter();
                    if ($risPoItemIds->isNotEmpty()) {
                        $hasAnyDa = true;
                        $hasPending = Mr::whereIn('po_item_id_fk', $risPoItemIds)->where('is_assigned', 0)->exists();
                        if ($hasPending) {
                            $allDAsScanned = false;
                        }
                    }
                }

                // Check PAR forms
                foreach ($p->parReceipts as $par) {
                    $parPoItemIds = $par->parItems->pluck('par_po_items_id_fk')->filter();
                    if ($parPoItemIds->isNotEmpty()) {
                        $hasAnyDa = true;
                        $hasPending = Mr::whereIn('po_item_id_fk', $parPoItemIds)->where('is_assigned', 0)->exists();
                        if ($hasPending) {
                            $allDAsScanned = false;
                        }
                    }
                }
            }

            // Supplies' logic placement (left blank as requested)
            // [SUPPLIES_PLACEHOLDER]

            if ($hasAnyDa && $allDAsScanned) {
                $pr->update(['scanned_at' => now()]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Items successfully claimed and recorded to your account.',
            'items' => $finalItems
        ], 200);
    }

    public function getUserItems(Request $request)
    {
        $user = Auth::user();

        $items = Mr::where('assigned_to', $user->user_id)
            ->where('is_assigned', 1)
            ->with(['poItem', 'images'])
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

                $paths = $item->images->pluck('image_path')->toArray();

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
                    'item_image' => empty($paths) ? null : $paths,
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

            // 1. Process Existing Images (delete those that are not in the existing_images request list)
            $existingPaths = $request->input('existing_images', []);

            // Handle JSON-encoded string from mobile app
            if (is_string($existingPaths)) {
                $existingPaths = json_decode($existingPaths, true) ?? [];
            }

            if (!is_array($existingPaths)) {
                $existingPaths = [$existingPaths];
            }

            // Find all current images in the database for this item
            $currentImages = \App\Models\MrItemImage::where('mr_id', $item->mr_id)->get();

            foreach ($currentImages as $dbImg) {
                // If a database image is not in the request's existing images list, delete it
                if (!in_array($dbImg->image_path, $existingPaths)) {
                    $fullPath = public_path($dbImg->image_path);
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                    $dbImg->delete();
                }
            }

            // Get count of remaining images
            $currentCount = \App\Models\MrItemImage::where('mr_id', $item->mr_id)->count();

            // 2. Process ALL New Uploads in one go
            if ($request->hasFile('item_image')) {
                $uploaded = $request->file('item_image');
                $files = is_array($uploaded) ? $uploaded : [$uploaded];

                foreach ($files as $file) {
                    if ($file->isValid()) {
                        if ($currentCount >= 5) {
                            break; // Stop uploading if 5-image limit is reached
                        }

                        $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                        $file->move(public_path('img/items'), $filename);

                        \App\Models\MrItemImage::create([
                            'mr_id' => $item->mr_id,
                            'image_path' => 'img/items/' . $filename
                        ]);

                        $currentCount++;
                    }
                }
            }

            // Retrieve updated list of image paths
            $updatedImages = \App\Models\MrItemImage::where('mr_id', $item->mr_id)->pluck('image_path')->toArray();

            return response()->json([
                'status' => 'success',
                'message' => 'Images synced successfully!',
                'all_images' => $updatedImages
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

            $imageRecord = \App\Models\MrItemImage::where('mr_id', $item->mr_id)
                ->where('image_path', $request->image_path)
                ->first();

            if ($imageRecord) {
                $fullPath = public_path($imageRecord->image_path);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }

                $imageRecord->delete();

                $updatedImages = \App\Models\MrItemImage::where('mr_id', $item->mr_id)->pluck('image_path')->toArray();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Image deleted successfully.',
                    'all_images' => $updatedImages
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

    public function lookupItem(Request $request)
    {
        try {
            $request->validate([
                'mr_qr_code' => 'required|string',
            ]);

            $qrCode = trim($request->mr_qr_code);
            $item = Mr::where('mr_qr_code', $qrCode)
                ->with(['assignedUser', 'images'])
                ->first();

            if (!$item) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item not found.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'item_name' => $item->item_name,
                    'specification' => $item->specification,
                    'owner' => $item->assignedUser ? $item->assignedUser->user_fullname : 'Unassigned',
                    'date_scanned' => $item->date_scanned,
                    'category' => $item->category,
                    'image' => $item->images->first() ? $item->images->first()->image_path : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}

