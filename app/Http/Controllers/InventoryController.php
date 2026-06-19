<?php

namespace App\Http\Controllers;

use App\Models\Mr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

        $mrItems = Mr::with(['assignedUser.departments'])->orderBy('date_scanned', 'desc')->get();

        $counts = [
            'all'             => $mrItems->count(),
            'equipment'       => $mrItems->where('category', 'Equipment')->count(),
            'semi_expendable' => $mrItems->where('category', 'Semi-Expendable')->count(),
            'supplies'        => $mrItems->where('category', 'Supply and Materials')->count(),
        ];

        return view('supply/pages/supply-inventory', compact('mrItems', 'counts'));
    }

    /**
     * Generate a QR code, overlay it onto a template image, and force download the merged PNG.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateLabel(Request $request)
    {
        // -------------------------------------------------------------
        // 1. Configuration & Tweakable Settings
        // -------------------------------------------------------------
        $qrWidth      = 260;               // Width of the QR code in pixels after resizing
        $qrHeight     = 245;               // Height of the QR code in pixels after resizing
        $xOffset      = -10;               // X coordinate on the background template where QR will be pasted
        $yOffset      = -10;               // Y coordinate on the background template where QR will be pasted
        $templateName = 'qr_template.png'; // File name of the template in public/img/qr_templates/

        // -------------------------------------------------------------
        // 2. Input Retrieval
        // -------------------------------------------------------------
        // Retrieve the QR code text or URL to encode from the request query string.
        // Falls back to a default example URL if not provided.
        $qrText = $request->query('mr_qr_code') ?: 'https://example.com';

        // -------------------------------------------------------------
        // 3. Temporary QR Code Generation via chillerlan/php-qrcode
        // -------------------------------------------------------------
        // Set options: High Error Correction (ECC_H), PNG output using GD, and transparent background disabled
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
        // 4. Background Template Verification & Manipulation via intervention/image
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

        $width = imagesx($gdImage);
        $height = imagesy($gdImage);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $colorIndex = imagecolorat($gdImage, $x, $y);
                $colorInfo = imagecolorsforindex($gdImage, $colorIndex);

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
        // The default alignment for insert() is TOP_LEFT.
        $backgroundImage->insert($qrImage, $xOffset, $yOffset);

        // Save the final merged PNG image into public/img/qr_stickers/.
        // The save() method automatically encodes based on the file extension (.png).
        $stickerName = 'sticker_' . uniqid() . '.png';
        $stickerPath = $qrStickersDir . '/' . $stickerName;
        $backgroundImage->save($stickerPath);

        // -------------------------------------------------------------
        // 5. Cleanup & File Download Response
        // -------------------------------------------------------------
        // Delete the raw QR code file from public/img/qr_codes/ since it is no longer needed
        if (file_exists($tempQrPath)) {
            @unlink($tempQrPath);
        }

        // Return a binary download response forcing the browser to download the merged PNG file
        return response()->download($stickerPath, $stickerName, [
            'Content-Type' => 'image/png',
        ]);
    }
}

