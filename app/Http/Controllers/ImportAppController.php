<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportAppController extends Controller
{
    public function showImportApp()
    {
        return view('head/pages/head-import-app');
    }

    public function importApp(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip the 2-row multi-line header
        fgetcsv($handle); // Row 1: main header
        fgetcsv($handle); // Row 2: sub-header (schedule columns)

        $userId = Auth::id();
        // This should be handled by a model
        $depId = DB::table('user_roles_tbl')
            ->join('roles_tbl', 'user_roles_tbl.role_id_fk', '=', 'roles_tbl.role_id')
            ->where('user_roles_tbl.user_id_fk', $userId)
            ->value('roles_tbl.role_dep_id_fk');

        DB::beginTransaction();
        try {
            // This should be handled by a model
            $appId = DB::table('app_tbl')->insertGetId([
                'app_status'                => 'Draft',
                'saved_by_user_id_fk'       => $userId,
                'app_prepared_by_name'      => $userId,
                'app_recommending_by_name'  => $userId,
                'app_approved_by_name'      => $userId,
                'app_dep_id_fk'             => $depId,
            ]);

            $insertedCount = 0;

            while (($row = fgetcsv($handle)) !== false) {
                // Skip rows with fewer than 13 columns
                if (count($row) < 13) continue;

                // The item name is in column 1 (index 1)
                $itemName = trim($row[1] ?? '');

                // Skip empty rows (no item name)
                if ($itemName === '') continue;

                // Skip department header rows (all uppercase department names)
                if (mb_strtoupper($itemName) === $itemName && !preg_match('/\d/', $itemName)) continue;

                // Skip TOTAL row
                $col8 = strtoupper(trim($row[8] ?? ''));
                if ($col8 === 'TOTAL') continue;

                // Skip signature block rows
                $skipKeywords = ['Prepared:', 'Recommending Approval:', 'Approved:', '[Name]', '[Designation]'];
                $shouldSkip = false;
                foreach ($skipKeywords as $keyword) {
                    if (stripos($itemName, $keyword) !== false) {
                        $shouldSkip = true;
                        break;
                    }
                }
                if ($shouldSkip) continue;

                // Parse and insert the data row
                // This should be handled by a model
                DB::table('app_items_tbl')->insert([
                    'app_id_fk'                => $appId,
                    'app_item_code'            => self::trimOrNull($row[0]),
                    'app_item_name'            => $itemName ?: null,
                    'app_item_pmo'             => self::trimOrNull($row[2]),
                    'app_item_mode'            => self::trimOrNull($row[3]),
                    'app_item_adspost'         => self::parseDate($row[4]),
                    'app_item_subopen'         => self::parseDate($row[5]),
                    'app_item_notice'          => self::parseDate($row[6]),
                    'app_item_contract'        => self::parseDate($row[7]),
                    'app_item_source_fund'     => self::trimOrNull($row[8]),
                    'app_item_estimated_total' => self::parseCurrency($row[9]),
                    'app_item_estimated_mooe'  => self::parseCurrency($row[10]),
                    'app_item_estimated_co'    => self::parseCurrency($row[11]),
                    'app_item_remarks'         => self::trimOrNull($row[12]),
                ]);

                $insertedCount++;
            }

            fclose($handle);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "APP imported successfully. {$insertedCount} item(s) added.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trim a string value and return null if empty.
     */
    private static function trimOrNull(?string $val): ?string
    {
        if ($val === null) return null;
        $trimmed = trim($val);
        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Parse a date string like "March 03, 2025" or "June 2, 2025" into Y-m-d.
     * Returns null if the value is empty or unparseable.
     */
    private static function parseDate(?string $val): ?string
    {
        if ($val === null) return null;
        $trimmed = trim($val);
        if ($trimmed === '') return null;

        try {
            return Carbon::parse($trimmed)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse a currency string like " ₱50,000.00 " or " 20,000.00 " into a float.
     * Returns null if the value is empty or non-numeric after cleaning.
     */
    private static function parseCurrency(?string $val): ?float
    {
        if ($val === null) return null;
        $trimmed = trim($val);
        if ($trimmed === '') return null;

        // Remove currency symbols, commas, and whitespace
        $cleaned = preg_replace('/[₱,\s]/', '', $trimmed);
        if ($cleaned === '' || !is_numeric($cleaned)) return null;

        return (float) $cleaned;
    }
}
