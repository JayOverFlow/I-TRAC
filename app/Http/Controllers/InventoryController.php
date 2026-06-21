<?php

namespace App\Http\Controllers;

use App\Models\Mr;
use App\Models\MrItemImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Mpdf\Mpdf as MpdfLib;

class InventoryController extends Controller
{
    public function showInventory()
    {
        $user = Auth::user();
        $activeRoleId = session('active_role_id');
        $activeRole = $user->roles->where('role_id', $activeRoleId)->first() ?? $user->roles->first();

        if (($activeRole?->gen_role) !== 'Supply') {
            return redirect()->route('404');
        }

        $mrItems = Mr::with(['assignedUser.departments', 'images'])->orderBy('date_scanned', 'desc')->get();

        $counts = [
            'all'             => $mrItems->count(),
            'equipment'       => $mrItems->where('category', 'Equipment')->count(),
            'semi_expendable' => $mrItems->where('category', 'Semi-Expendable')->count(),
            'supplies'        => $mrItems->where('category', 'Supply and Materials')->count(),
        ];

        return view('supply/pages/supply-inventory', compact('mrItems', 'counts'));
    }

    /**
     * Generate QR code sticker(s), place them into the A6 PDF grid,
     * and return JSON with download URLs.
     *
     * Supports multiple sizes (Small, Medium, Large) and layouts (with/without text).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateLabel(Request $request)
    {
        // -------------------------------------------------------------
        // 1. Input Retrieval & Size/Layout Configuration Mapping
        // -------------------------------------------------------------
        $size   = $request->query('label_size') ?: 'Small';
        $layout = $request->query('qr_layout') ?: 'layout_1';
        $qrText = $request->query('mr_qr_code') ?: 'https://example.com';
        $stickerQuantity = max(1, (int) ($request->query('sticker_quantity') ?: 1));

        // Dictionary defining parameters for each of the 5 configurations.
        // You can manually adjust qr_width, qr_height, x_offset, and y_offset below.
        $configs = [
            'Small_layout_1' => [
                'template'  => 'small.png',
                'sheet'     => '2X2', //QR ONLY
                'cols'      => 4,
                'rows'      => 6,
                'desc'      => 'Small_NoText',
                'qr_width'  => 298,
                'qr_height' => 253,
                'x_offset'  => -25,
                'y_offset'  => -21,
            ],
            'Medium_layout_1' => [
                'template'  => 'medium-qr.png',
                'sheet'     => '3X3', //QR ONLY
                'cols'      => 3,
                'rows'      => 4,
                'desc'      => 'Medium_NoText',
                'qr_width'  => 455,
                'qr_height' => 380,
                'x_offset'  => -38,
                'y_offset'  => -32,
            ],
            'Large_layout_1' => [
                'template'  => 'large-qr.png',
                'sheet'     => '4X4', //QR ONLY
                'cols'      => 2,
                'rows'      => 3,
                'desc'      => 'Large_NoText',
                'qr_width'  => 620,
                'qr_height' => 517,
                'x_offset'  => -50,
                'y_offset'  => -35,
            ],
            'Medium_layout_2' => [
                'template'  => 'medium-qr-text.png',
                'sheet'     => '3X4.5', //WITH TEXT
                'cols'      => 3,
                'rows'      => 3,
                'desc'      => 'Medium_WithText',
                'qr_width'  => 470,
                'qr_height' => 387,
                'x_offset'  => -45,
                'y_offset'  => -35,
            ],
            'Large_layout_2' => [
                'template'  => 'large-qr-text.png',
                'sheet'     => '4X6', //WITH TEXT
                'cols'      => 2,
                'rows'      => 2,
                'desc'      => 'Large_WithText',
                'qr_width'  => 625,
                'qr_height' => 530,
                'x_offset'  => -52,
                'y_offset'  => -44,
            ],
        ];

        // Resolve selected configuration; fallback to Small Layout 1 if mismatch
        $configKey = $size . '_' . $layout;
        $config    = isset($configs[$configKey]) ? $configs[$configKey] : $configs['Small_layout_1'];

        $qrWidth      = $config['qr_width'];
        $qrHeight     = $config['qr_height'];
        $xOffset      = $config['x_offset'];
        $yOffset      = $config['y_offset'];
        $templateName = $config['template'];
        $gridCols     = $config['cols'];
        $gridRows     = $config['rows'];
        $desc         = $config['desc'];
        $stickersPerPage = $gridCols * $gridRows;

        // -------------------------------------------------------------
        // 2. Temporary QR Code Generation via chillerlan/php-qrcode
        // -------------------------------------------------------------
        $options = new QROptions([
            'outputInterface'  => QRGdImagePNG::class,
            'scale'            => 10,
            'eccLevel'         => EccLevel::H,
            'imageTransparent' => true,
        ]);

        $qrcode = new QRCode($options);

        // Ensure directories for storing QR codes and stickers exist
        $qrCodesDir    = public_path('img/qr_codes');
        $qrStickersDir = public_path('img/qr_stickers');

        if (!file_exists($qrCodesDir)) {
            mkdir($qrCodesDir, 0755, true);
        }
        if (!file_exists($qrStickersDir)) {
            mkdir($qrStickersDir, 0755, true);
        }

        // Generate a unique temporary path for the raw QR code PNG
        $tempQrName = 'temp_qr_' . uniqid() . '.png';
        $tempQrPath = $qrCodesDir . '/' . $tempQrName;

        // Render and write the raw QR code to the temporary file path
        $qrcode->render($qrText, $tempQrPath);

        // -------------------------------------------------------------
        // 3. Background Template Verification & Manipulation via intervention/image
        // -------------------------------------------------------------
        $templatePath = public_path('img/qr_templates/' . $templateName);
        if (!file_exists($templatePath)) {
            // Clean up temporary raw QR code file if template doesn't exist
            if (file_exists($tempQrPath)) {
                @unlink($tempQrPath);
            }
            abort(404, "Background template image not found at {$templatePath}");
        }

        // Initialize Intervention Image Manager with the GD driver
        $manager = new ImageManager(new Driver());

        // Read background template and temporary QR code into Image instances using decode()
        $backgroundImage = $manager->decode($templatePath);
        $qrImage         = $manager->decode($tempQrPath);

        // Access the raw GD image resource to convert white pixels to true transparent alpha pixels.
        // This avoids GD losing transparency when resizing color-keyed palette images.
        $gdImage = $qrImage->core()->first()->native();
        imagealphablending($gdImage, false);
        imagesavealpha($gdImage, true);

        $width  = imagesx($gdImage);
        $height = imagesy($gdImage);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $colorIndex = imagecolorat($gdImage, $x, $y);
                $colorInfo  = imagecolorsforindex($gdImage, $colorIndex);

                // If the pixel is pure white (used for background and light modules), set it to transparent
                if ($colorInfo['red'] === 255 && $colorInfo['green'] === 255 && $colorInfo['blue'] === 255) {
                    $transparentColor = imagecolorallocatealpha($gdImage, 255, 255, 255, 127);
                    imagesetpixel($gdImage, $x, $y, $transparentColor);
                }
            }
        }

        // Resize the transparent QR code image using the configurable dimensions
        $qrImage->resize($qrWidth, $qrHeight);

        // Insert the resized QR code onto the background template at the defined X and Y offsets.
        $backgroundImage->insert($qrImage, $xOffset, $yOffset);

        // Save the final merged PNG image (the single sticker) into public/img/qr_stickers/.
        $stickerName = 'sticker_' . uniqid() . '.png';
        $stickerPath = $qrStickersDir . '/' . $stickerName;
        $backgroundImage->save($stickerPath);

        // Clean up the temporary raw QR code file since it's no longer needed
        if (file_exists($tempQrPath)) {
            @unlink($tempQrPath);
        }

        // -------------------------------------------------------------
        // 4. Batch PDF Generation using mPDF directly
        // -------------------------------------------------------------
        // Calculate how many A6 pages (PDF files) are needed
        $totalPages = (int) ceil($stickerQuantity / $stickersPerPage);

        // Track which sticker number we're on across all pages
        $stickerIndex = 0;

        // Collect the public-accessible URLs for all generated PDFs
        $pdfUrls = [];

        // A6 dimensions: 105mm × 148mm. Each cell size is derived by dividing
        // the page area evenly across the grid (cols × rows).
        $cellWidth  = 105 / $gridCols;
        $cellHeight = 148 / $gridRows;

        for ($page = 0; $page < $totalPages; $page++) {
            // Create a new mPDF instance with A6 portrait, zero margins
            $mpdf = new MpdfLib([
                'format'        => 'A6',
                'orientation'   => 'P',
                'margin_left'   => 0,
                'margin_right'  => 0,
                'margin_top'    => 0,
                'margin_bottom' => 0,
                'margin_header' => 0,
                'margin_footer' => 0,
                'tempDir'       => storage_path('app/mpdf_tmp'),
            ]);

            // Determine how many stickers to place on this specific page
            $stickersOnThisPage = min($stickersPerPage, $stickerQuantity - $stickerIndex);

            // Build an HTML table that fills the entire A6 page with sticker images.
            $html  = '<table style="width:105mm; height:148mm; border-collapse:collapse; table-layout:fixed;">';
            $placed = 0;

            for ($r = 0; $r < $gridRows; $r++) {
                $html .= '<tr>';
                for ($c = 0; $c < $gridCols; $c++) {
                    $html .= '<td style="width:' . $cellWidth . 'mm; height:' . $cellHeight . 'mm; text-align:center; vertical-align:middle; padding:0; margin:0;">';

                    // Only insert an image if we still have stickers to place
                    if ($placed < $stickersOnThisPage) {
                        $html .= '<img src="' . $stickerPath . '" style="width:' . $cellWidth . 'mm; height:' . $cellHeight . 'mm; display:block;" />';
                        $placed++;
                        $stickerIndex++;
                    }

                    $html .= '</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</table>';

            // Write the HTML table into the mPDF document and save as PDF
            $mpdf->WriteHTML($html);

            // Save using a descriptive file name containing size and layout descriptors
            $pdfName = 'labels_' . $desc . '_page' . ($page + 1) . '_' . uniqid() . '.pdf';
            $pdfPath = $qrStickersDir . '/' . $pdfName;
            $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

            // Build the public URL for the generated PDF
            $pdfUrls[] = asset('img/qr_stickers/' . $pdfName);
        }

        // Clean up the single sticker PNG since it has been embedded into the PDFs
        if (file_exists($stickerPath)) {
            @unlink($stickerPath);
        }

        // -------------------------------------------------------------
        // 5. JSON Response with Download URLs
        // -------------------------------------------------------------
        return response()->json([
            'pdf_urls' => $pdfUrls,
        ]);
    }

    /**
     * Upload an image for an item, appending it to the mr_item_img_tbl table.
     * Max 5 images.
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'mr_id' => 'required|integer',
                'item_image' => 'required|image|max:5120', // Limit size to 5MB
            ]);

            $item = Mr::where('mr_id', $request->mr_id)->first();

            if (!$item) {
                return response()->json(['status' => 'error', 'message' => 'Item not found.'], 404);
            }

            // Check if limit of 5 is exceeded
            $currentCount = \App\Models\MrItemImage::where('mr_id', $request->mr_id)->count();
            if ($currentCount >= 5) {
                return response()->json(['status' => 'error', 'message' => 'Maximum limit of 5 images reached.'], 400);
            }

            // Process New Upload
            if ($request->hasFile('item_image')) {
                $file = $request->file('item_image');
                if ($file->isValid()) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('img/items'), $filename);

                    // Create new database record
                    \App\Models\MrItemImage::create([
                        'mr_id' => $request->mr_id,
                        'image_path' => 'img/items/' . $filename
                    ]);
                }
            }

            // Return images with full asset URLs for rendering
            $images = \App\Models\MrItemImage::where('mr_id', $request->mr_id)->get();
            $formattedImages = $images->map(function($img) {
                return [
                    'url' => asset($img->image_path),
                    'path' => $img->image_path
                ];
            })->all();

            return response()->json([
                'status' => 'success',
                'message' => 'Image uploaded successfully!',
                'images' => $formattedImages
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a specific image from the mr_item_img_tbl table and delete its file.
     */
    public function deleteImage(Request $request)
    {
        try {
            $request->validate([
                'mr_id' => 'required|integer',
                'image_path' => 'required|string',
            ]);

            $item = Mr::where('mr_id', $request->mr_id)->first();

            if (!$item) {
                return response()->json(['status' => 'error', 'message' => 'Item not found.'], 404);
            }

            // Find image record
            $imageRecord = \App\Models\MrItemImage::where('mr_id', $request->mr_id)
                ->where('image_path', $request->image_path)
                ->first();

            if ($imageRecord) {
                $fullPath = public_path($imageRecord->image_path);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }

                // Delete database record
                $imageRecord->delete();

                // Return updated images list
                $images = \App\Models\MrItemImage::where('mr_id', $request->mr_id)->get();
                $formattedImages = $images->map(function($img) {
                    return [
                        'url' => asset($img->image_path),
                        'path' => $img->image_path
                    ];
                })->all();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Image deleted successfully.',
                    'images' => $formattedImages
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'Image path not found in item record.'], 404);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}
