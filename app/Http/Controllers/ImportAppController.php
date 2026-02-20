<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImportAppController extends Controller
{
    public function showImportApp() {
        return view('head/pages/head-import-app');
    }

    public function importApp(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // skip header row

        $userId = Auth::id();
        $depId = DB::table('user_roles_tbl')
            ->join('roles_tbl', 'user_roles_tbl.role_id_fk', '=', 'roles_tbl.role_id')
            ->where('user_roles_tbl.user_id_fk', $userId)
            ->value('roles_tbl.role_dep_id_fk');

        DB::beginTransaction();
        try {
            $appId = DB::table('app_tbl')->insertGetId([
                'app_status'                => 'Draft',
                'saved_by_user_id_fk'       => $userId,
                'app_prepared_by_name'      => $userId,
                'app_recommending_by_name'  => $userId,
                'app_approved_by_name'      => $userId,
                'app_dep_id_fk'             => $depId,
            ]);

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 13) continue; // skip malformed rows

                DB::table('app_items_tbl')->insert([
                    'app_id_fk'                => $appId,
                    'app_item_name'            => $row[0] ?? null,
                    'app_item_pmo'             => $row[1] ?? null,
                    'app_item_mode'            => $row[2] ?? null,
                    'app_item_estimated_total' => $row[3] ?? null,
                    'app_item_estimated_mooe'  => $row[4] ?? null,
                    'app_item_estimated_co'    => $row[5] ?? null,
                    'app_item_remarks'         => $row[6] ?? null,
                    'app_item_adspost'         => $row[7] ?: null,
                    'app_item_subopen'         => $row[8] ?: null,
                    'app_item_notice'          => $row[9] ?: null,
                    'app_item_contract'        => $row[10] ?: null,
                    'app_item_source_fund'     => $row[11] ?? null,
                    'app_item_code'            => $row[12] ?? null,
                ]);
            }

            fclose($handle);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'APP imported successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return response()->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
}
