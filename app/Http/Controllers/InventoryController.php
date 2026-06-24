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
        $stickerQuantity = max(1, (int) ($request->query('sticker_quantity') ?: 1));
        $paperSize       = strtoupper($request->query('paper_size') ?: 'A6');
        if (!in_array($paperSize, ['A6', 'A4'])) {
            $paperSize = 'A6';
        }

        // Get mr_id or mr_ids from query
        $mrIdsInput = $request->query('mr_ids') ?: $request->query('mr_id');
        $items = collect();

        if ($mrIdsInput) {
            if (is_array($mrIdsInput)) {
                $mrIds = $mrIdsInput;
            } else {
                $mrIds = explode(',', $mrIdsInput);
            }
            $mrIds = array_filter(array_map('intval', $mrIds));

            if (!empty($mrIds)) {
                $items = Mr::whereIn('mr_id', $mrIds)->get();
            }
        }

        // Fallback to mr_qr_code query param if no items found (backward compatibility / testing)
        if ($items->isEmpty() && $request->has('mr_qr_code')) {
            $items = collect([
                (object)[
                    'mr_id' => null,
                    'mr_qr_code' => $request->query('mr_qr_code')
                ]
            ]);
        }

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

        // A4 configuration dictionary — same PNG templates, larger grid (more stickers per page).
        // Grid dimensions derived from Excel-for-QR-Labels_A4.xlsx sheet cell counts.
        $a4Configs = [
            'Small_layout_1'  => [
                'template'  => 'small.png',
                'sheet'     => '2X2',
                'cols'      => 10,
                'rows'      => 14,
                'desc'      => 'A4_Small_NoText',
                'qr_width'  => 298,
                'qr_height' => 253,
                'x_offset'  => -25,
                'y_offset'  => -21,
            ],
            'Medium_layout_1' => [
                'template'  => 'medium-qr.png',
                'sheet'     => '3X3',
                'cols'      => 6,
                'rows'      => 9,
                'desc'      => 'A4_Medium_NoText',
                'qr_width'  => 455,
                'qr_height' => 380,
                'x_offset'  => -38,
                'y_offset'  => -32,
            ],
            'Large_layout_1'  => [
                'template'  => 'large-qr.png',
                'sheet'     => '4X4',
                'cols'      => 4,
                'rows'      => 7,
                'desc'      => 'A4_Large_NoText',
                'qr_width'  => 620,
                'qr_height' => 517,
                'x_offset'  => -50,
                'y_offset'  => -35,
            ],
            'Medium_layout_2' => [
                'template'  => 'medium-qr-text.png',
                'sheet'     => '3X4.5',
                'cols'      => 6,
                'rows'      => 6,
                'desc'      => 'A4_Medium_WithText',
                'qr_width'  => 470,
                'qr_height' => 387,
                'x_offset'  => -45,
                'y_offset'  => -35,
            ],
            'Large_layout_2'  => [
                'template'  => 'large-qr-text.png',
                'sheet'     => '4X6',
                'cols'      => 4,
                'rows'      => 4,
                'desc'      => 'A4_Large_WithText',
                'qr_width'  => 625,
                'qr_height' => 530,
                'x_offset'  => -52,
                'y_offset'  => -44,
            ],
        ];

        // Resolve selected configuration; fallback to Small Layout 1 if mismatch.
        // Source dict is chosen based on the requested paper size.
        $configKey    = $size . '_' . $layout;
        $configSource = ($paperSize === 'A4') ? $a4Configs : $configs;
        $config       = isset($configSource[$configKey]) ? $configSource[$configKey] : $configSource['Small_layout_1'];

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

        // -------------------------------------------------------------
        // 3. Background Template Verification & Manipulation via intervention/image
        // -------------------------------------------------------------
        $templatePath = public_path('img/qr_templates/' . $templateName);
        if (!file_exists($templatePath)) {
            abort(404, "Background template image not found at {$templatePath}");
        }

        // Initialize Intervention Image Manager with the GD driver
        $manager = new ImageManager(new Driver());

        $stickerPaths = [];
        $tempFilesToClean = [];

        foreach ($items as $item) {
            $qrText = $item->mr_qr_code;
            if (empty($qrText)) {
                continue;
            }

            // Generate a unique temporary path for the raw QR code PNG
            $tempQrName = 'temp_qr_' . uniqid() . '.png';
            $tempQrPath = $qrCodesDir . '/' . $tempQrName;

            // Render and write the raw QR code to the temporary file path
            $qrcode->render($qrText, $tempQrPath);
            $tempFilesToClean[] = $tempQrPath;

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
            
            $tempFilesToClean[] = $stickerPath;

            // Add the sticker path to our list repeated sticker_quantity times
            for ($i = 0; $i < $stickerQuantity; $i++) {
                $stickerPaths[] = $stickerPath;
            }
        }

        if (empty($stickerPaths)) {
            return response()->json([
                'error' => 'No valid QR codes found to generate.',
                'pdf_urls' => []
            ], 400);
        }

        // -------------------------------------------------------------
        // 4. Batch PDF Generation using mPDF directly
        // -------------------------------------------------------------
        $totalStickersCount = count($stickerPaths);
        // Calculate how many A6 pages (PDF files) are needed
        $totalPages = (int) ceil($totalStickersCount / $stickersPerPage);

        // Track which sticker number we're on across all pages
        $stickerIndex = 0;

        // Collect the public-accessible URLs for all generated PDFs
        $pdfUrls = [];

        // Page dimensions in mm (A6: 105×148mm | A4: 210×297mm).
        $pageWidth  = ($paperSize === 'A4') ? 210 : 105;
        $pageHeight = ($paperSize === 'A4') ? 297 : 148;

        // Header height adjustments (reduced height to text height to maximize sticker space)
        $headerHeight = ($paperSize === 'A4') ? 4.5 : 3.5;
        $gridHeight   = $pageHeight - $headerHeight;
        $scaleFactor  = $gridHeight / $pageHeight;

        // Cell size is derived by dividing the remaining grid area evenly across the grid (cols × rows).
        $cellWidth  = $pageWidth  / $gridCols;
        $cellHeight = $gridHeight / $gridRows;

        // Scale image width and height down proportionally and leave a small 2% padding/gap inside cell
        $imgWidth  = $cellWidth * $scaleFactor * 0.98;
        $imgHeight = $cellHeight * 0.98;

        for ($page = 0; $page < $totalPages; $page++) {
            // Create a new mPDF instance with the selected paper size, portrait, zero margins
            $mpdf = new MpdfLib([
                'format'        => $paperSize,
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
            $stickersOnThisPage = min($stickersPerPage, $totalStickersCount - $stickerIndex);

            // Build header text & div
            $itemName = 'Item QR Label';
            if ($items->isNotEmpty()) {
                $firstItem = $items->first();
                $itemName = isset($firstItem->item_name) ? $firstItem->item_name : 'Item QR Label';
            }
            $layoutLabel = ($layout === 'layout_2') ? 'With Text' : 'No Text';
            $headerText  = htmlspecialchars($itemName)
                         . ' &nbsp;&middot;&nbsp; ' . htmlspecialchars($size)
                         . ' / ' . $layoutLabel
                         . ' &nbsp;&middot;&nbsp; ' . $stickerQuantity . ' sticker' . ($stickerQuantity !== 1 ? 's' : '');

            // Compact styling matching paper size (smaller font and padding to maximize sticker layout space)
            if ($paperSize === 'A4') {
                $headerHtml = '<div style="width:' . $pageWidth . 'mm; background:#e8e8e8; padding:0.5mm 2mm;'
                            . ' font-size:7pt; font-weight:bold; font-family:Arial,sans-serif;'
                            . ' color:#222; box-sizing:border-box; height:' . $headerHeight . 'mm; overflow:hidden;">'
                            . $headerText . '</div>';
            } else {
                $headerHtml = '<div style="width:' . $pageWidth . 'mm; background:#e8e8e8; padding:0.3mm 1mm;'
                            . ' font-size:6.5pt; font-weight:bold; font-family:Arial,sans-serif;'
                            . ' color:#222; box-sizing:border-box; height:' . $headerHeight . 'mm; overflow:hidden;">'
                            . $headerText . '</div>';
            }

            // Determine how many rows are needed for the stickers on this page
            $rowsOnThisPage = min($gridRows, (int) ceil($stickersOnThisPage / $gridCols));

            // Build an HTML table that fits the required sticker rows.
            $html  = $headerHtml;
            $html .= '<table style="width:' . $pageWidth . 'mm; border-collapse:collapse; table-layout:fixed;">';
            $placed = 0;

            for ($r = 0; $r < $rowsOnThisPage; $r++) {
                $html .= '<tr>';
                for ($c = 0; $c < $gridCols; $c++) {
                    if ($placed < $stickersOnThisPage) {
                        $currentStickerPath = $stickerPaths[$stickerIndex];
                        $html .= '<td style="width:' . $cellWidth . 'mm; height:' . $cellHeight . 'mm;'
                               . ' text-align:center; vertical-align:middle; padding:0; margin:0;'
                               . ' border:0.1mm solid #000000;">';
                        $html .= '<img src="' . $currentStickerPath . '" style="width:' . $imgWidth . 'mm;'
                               . ' height:' . $imgHeight . 'mm; display:block; margin:auto;" />';
                        $placed++;
                        $stickerIndex++;
                    } else {
                        // Empty cell has no cutting guide border
                        $html .= '<td style="width:' . $cellWidth . 'mm; height:' . $cellHeight . 'mm;'
                               . ' text-align:center; vertical-align:middle; padding:0; margin:0;'
                               . ' border:none;">';
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

        // Clean up all temporary files (raw QR codes and merged stickers)
        foreach ($tempFilesToClean as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
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

    // =========================================================
    // PRIVATE HELPER — used by exportQueuePdf
    // =========================================================

    /**
     * Generates a merged sticker PNG: QR code rendered and overlaid onto the template background.
     *
     * @param  string  $qrText      The QR code data string to encode.
     * @param  array   $config      A config entry from getA4Configs() dict.
     * @param  array   &$tempFiles  Accumulates file paths to be cleaned up by the caller.
     * @return string|null          Absolute path of the saved sticker PNG, or null on failure.
     */
    private function generateStickerPng(string $qrText, array $config, array &$tempFiles): ?string
    {
        $qrCodesDir    = public_path('img/qr_codes');
        $qrStickersDir = public_path('img/qr_stickers');

        foreach ([$qrCodesDir, $qrStickersDir] as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $templatePath = public_path('img/qr_templates/' . $config['template']);
        if (!file_exists($templatePath)) {
            return null;
        }

        // Render raw QR code to a temp PNG
        $options = new QROptions([
            'outputInterface'  => QRGdImagePNG::class,
            'scale'            => 10,
            'eccLevel'         => EccLevel::H,
            'imageTransparent' => true,
        ]);
        $qrcode     = new QRCode($options);
        $tempQrPath = $qrCodesDir . '/temp_qr_' . uniqid() . '.png';
        $qrcode->render($qrText, $tempQrPath);
        $tempFiles[] = $tempQrPath;

        // Load via Intervention Image
        $manager         = new ImageManager(new Driver());
        $backgroundImage = $manager->decode($templatePath);
        $qrImage         = $manager->decode($tempQrPath);

        // Convert white pixels → transparent (preserves QR clarity on coloured backgrounds)
        $gdImage = $qrImage->core()->first()->native();
        imagealphablending($gdImage, false);
        imagesavealpha($gdImage, true);
        $w = imagesx($gdImage);
        $h = imagesy($gdImage);
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $ci = imagecolorat($gdImage, $x, $y);
                $c  = imagecolorsforindex($gdImage, $ci);
                if ($c['red'] === 255 && $c['green'] === 255 && $c['blue'] === 255) {
                    imagesetpixel($gdImage, $x, $y, imagecolorallocatealpha($gdImage, 255, 255, 255, 127));
                }
            }
        }

        // Resize QR and overlay onto background
        $qrImage->resize($config['qr_width'], $config['qr_height']);
        $backgroundImage->insert($qrImage, $config['x_offset'], $config['y_offset']);

        // Save merged sticker PNG
        $stickerPath = $qrStickersDir . '/sticker_' . uniqid() . '.png';
        $backgroundImage->save($stickerPath);
        $tempFiles[] = $stickerPath;

        return $stickerPath;
    }

    /**
     * Returns the A4 sticker configuration dictionary.
     * Identical to the inline $a4Configs array inside generateLabel(),
     * extracted here so exportQueuePdf() can share it without duplication.
     */
    private function getA4Configs(): array
    {
        return [
            'Small_layout_1'  => ['template' => 'small.png',          'cols' => 10, 'rows' => 14, 'qr_width' => 298, 'qr_height' => 253, 'x_offset' => -25, 'y_offset' => -21],
            'Medium_layout_1' => ['template' => 'medium-qr.png',      'cols' => 6,  'rows' => 9,  'qr_width' => 455, 'qr_height' => 380, 'x_offset' => -38, 'y_offset' => -32],
            'Large_layout_1'  => ['template' => 'large-qr.png',       'cols' => 4,  'rows' => 7,  'qr_width' => 620, 'qr_height' => 517, 'x_offset' => -50, 'y_offset' => -35],
            'Medium_layout_2' => ['template' => 'medium-qr-text.png', 'cols' => 6,  'rows' => 6,  'qr_width' => 470, 'qr_height' => 387, 'x_offset' => -45, 'y_offset' => -35],
            'Large_layout_2'  => ['template' => 'large-qr-text.png',  'cols' => 4,  'rows' => 4,  'qr_width' => 625, 'qr_height' => 530, 'x_offset' => -52, 'y_offset' => -44],
        ];
    }

    // =========================================================
    // EXPORT QUEUE — Session-based batch multi-item PDF export
    // =========================================================

    /**
     * Add one item + its A4 label config to the export queue stored in the session.
     */
    public function addToQueue(Request $request)
    {
        $request->validate([
            'mr_id'           => 'required|integer',
            'label_size'      => 'required|string',
            'qr_layout'       => 'required|string',
            'sticker_quantity' => 'required|integer|min:1',
        ]);

        $item = Mr::find($request->mr_id);
        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Item not found.'], 404);
        }

        $queue   = session('qr_export_queue', []);
        $queue[] = [
            'mr_id'           => $item->mr_id,
            'item_name'       => $item->item_name,
            'mr_qr_code'      => $item->mr_qr_code,
            'label_size'      => $request->label_size,
            'qr_layout'       => $request->qr_layout,
            'sticker_quantity' => (int) $request->sticker_quantity,
        ];
        session(['qr_export_queue' => $queue]);

        return response()->json([
            'status'  => 'success',
            'message' => '"' . $item->item_name . '" added to Export Queue.',
            'count'   => count($queue),
        ]);
    }

    /**
     * Return the current export queue as JSON.
     */
    public function getQueue(Request $request)
    {
        $queue = session('qr_export_queue', []);
        return response()->json([
            'queue' => array_values($queue),
            'count' => count($queue),
        ]);
    }

    /**
     * Remove one entry from the queue by its 0-based index.
     */
    public function removeFromQueue(Request $request, int $index)
    {
        $queue = session('qr_export_queue', []);
        if (isset($queue[$index])) {
            array_splice($queue, $index, 1);
        }
        $queue = array_values($queue);
        session(['qr_export_queue' => $queue]);

        return response()->json([
            'status' => 'success',
            'queue'  => $queue,
            'count'  => count($queue),
        ]);
    }

    /**
     * Update an existing entry in the export queue by its 0-based index.
     */
    public function updateQueue(Request $request, int $index)
    {
        $request->validate([
            'label_size'       => 'required|string',
            'qr_layout'        => 'required|string',
            'sticker_quantity' => 'required|integer|min:1',
        ]);

        $queue = session('qr_export_queue', []);
        if (!isset($queue[$index])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Queue item not found.'
            ], 404);
        }

        $queue[$index]['label_size']       = $request->label_size;
        $queue[$index]['qr_layout']        = $request->qr_layout;
        $queue[$index]['sticker_quantity'] = (int) $request->sticker_quantity;

        session(['qr_export_queue' => $queue]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Export queue entry updated successfully.',
            'count'   => count($queue),
        ]);
    }

    /**
     * Clear the entire export queue.
     */
    public function clearQueue(Request $request)
    {
        session(['qr_export_queue' => []]);
        return response()->json(['status' => 'success', 'count' => 0]);
    }

    /**
     * Generate and return a single A4 PDF containing all queued items.
     *
     * Each item gets its own labelled header + sticker grid block.
     * Items may have different size/layout configurations in the same PDF.
     * mPDF handles page breaks automatically across item blocks.
     */
    public function exportQueuePdf(Request $request)
    {
        $queue = session('qr_export_queue', []);

        if (empty($queue)) {
            return response()->json(['error' => 'Export Queue is empty.'], 400);
        }

        $a4Configs     = $this->getA4Configs();
        $qrStickersDir = public_path('img/qr_stickers');
        $tempFiles     = [];
        $allHtml       = '';

        foreach ($queue as $entry) {
            $configKey = $entry['label_size'] . '_' . $entry['qr_layout'];
            $config    = $a4Configs[$configKey] ?? $a4Configs['Small_layout_1'];

            $gridCols = $config['cols'];
            $gridRows = $config['rows'];
            $qty      = (int) $entry['sticker_quantity'];

            // Build the sticker PNG for this item
            $stickerPath = $this->generateStickerPng($entry['mr_qr_code'], $config, $tempFiles);
            if (!$stickerPath) {
                continue;
            }

            // Header height adjustments (A4 only, reduced height to text height to maximize sticker space)
            $headerHeight = 4.5;
            $gridHeight   = 297 - $headerHeight;
            $scaleFactor  = $gridHeight / 297;

            // Cell dimensions on A4 (210×297mm page, but using gridHeight)
            $cellWidth  = 210 / $gridCols;
            $cellHeight = $gridHeight / $gridRows;

            // Scale image width and height down proportionally and leave a small 2% padding/gap inside cell
            $imgWidth  = $cellWidth * $scaleFactor * 0.98;
            $imgHeight = $cellHeight * 0.98;

            // Item header bar — sits above the sticker table, outside the grid
            $layoutLabel = ($entry['qr_layout'] === 'layout_2') ? 'With Text' : 'No Text';
            $headerText  = htmlspecialchars($entry['item_name'])
                         . ' &nbsp;&middot;&nbsp; ' . htmlspecialchars($entry['label_size'])
                         . ' / ' . $layoutLabel
                         . ' &nbsp;&middot;&nbsp; ' . $qty . ' sticker' . ($qty !== 1 ? 's' : '');

            $allHtml .= '<div style="width:210mm; background:#e8e8e8; padding:0.5mm 2mm;'
                      . ' font-size:7pt; font-weight:bold; font-family:Arial,sans-serif;'
                      . ' color:#222; box-sizing:border-box; height:' . $headerHeight . 'mm; overflow:hidden;">'
                      . $headerText . '</div>';

            // Sticker table — one row at a time so mPDF can break pages naturally
            $placed = 0;
            while ($placed < $qty) {
                // Determine how many rows are needed for the remaining stickers of this item on this page
                $remaining = $qty - $placed;
                $rowsOnThisPage = min($gridRows, (int) ceil($remaining / $gridCols));

                $allHtml .= '<table style="width:210mm; border-collapse:collapse; table-layout:fixed;">';

                for ($r = 0; $r < $rowsOnThisPage; $r++) {
                    $allHtml .= '<tr>';
                    for ($c = 0; $c < $gridCols; $c++) {
                        if ($placed < $qty) {
                            $allHtml .= '<td style="width:' . $cellWidth . 'mm; height:' . $cellHeight . 'mm;'
                                     . ' text-align:center; vertical-align:middle; padding:0; margin:0;'
                                     . ' border:0.1mm solid #000000;">';
                            $allHtml .= '<img src="' . $stickerPath . '" style="width:' . $imgWidth . 'mm;'
                                     . ' height:' . $imgHeight . 'mm; display:block; margin:auto;" />';
                            $placed++;
                        } else {
                            $allHtml .= '<td style="width:' . $cellWidth . 'mm; height:' . $cellHeight . 'mm;'
                                     . ' text-align:center; vertical-align:middle; padding:0; margin:0;'
                                     . ' border:none;">';
                        }
                        $allHtml .= '</td>';
                    }
                    $allHtml .= '</tr>';
                }

                $allHtml .= '</table>';
            }

            // Spacer between items
            $allHtml .= '<div style="height:3mm;"></div>';
        }

        if (empty($allHtml)) {
            return response()->json(['error' => 'No valid QR codes found in queue.'], 400);
        }

        // Render the combined PDF
        $mpdf = new MpdfLib([
            'format'        => 'A4',
            'orientation'   => 'P',
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'tempDir'       => storage_path('app/mpdf_tmp'),
        ]);

        $mpdf->WriteHTML($allHtml);

        $pdfName = 'export_queue_' . uniqid() . '.pdf';
        $pdfPath = $qrStickersDir . '/' . $pdfName;
        $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

        // Cleanup temp files
        foreach ($tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        // Clear queue after successful export
        session(['qr_export_queue' => []]);

        return response()->json([
            'pdf_url' => asset('img/qr_stickers/' . $pdfName),
        ]);
    }
}
