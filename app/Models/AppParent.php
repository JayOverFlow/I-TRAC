<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AppItem;
use App\Models\User;

class AppParent extends Model
{
    protected $table = 'app_tbl';
    protected $primaryKey = 'app_id';

    protected $fillable = [
        'app_title',
        'saved_by_user_id_fk',
        'app_prepared_by_name',
        'app_prepared_by_designation',
        'app_recommending_by_name',
        'app_recommending_by_designation',
        'app_approved_by_name',
        'app_approved_by_designation',
        'app_dep_id_fk',
        'app_status',
        'app_unique_code',
        'app_total',
        'utilized_budget',
        'is_active',
        'app_year',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function appItems()
    {
        return $this->hasMany(AppItem::class, 'app_id_fk', 'app_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'saved_by_user_id_fk', 'user_id');
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PrParent::class, 'app_id_fk', 'app_id');
    }

    public static function recalculateUtilizedBudget($appId)
    {
        $utilizedBudget = \Illuminate\Support\Facades\DB::table('po_items_tbl')
            ->join('po_tbl', 'po_items_tbl.po_id_fk', '=', 'po_tbl.po_id')
            ->join('pr_tbl', 'po_tbl.pr_id_fk', '=', 'pr_tbl.pr_id')
            ->where('pr_tbl.app_id_fk', $appId)
            ->where(function($query) {
                $query->whereExists(function($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('iar_tbl')
                      ->whereColumn('iar_tbl.iar_po_id_fk', 'po_tbl.po_id');
                })
                ->orWhereExists(function($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('ris_tbl')
                      ->whereColumn('ris_tbl.po_id_fk', 'po_tbl.po_id');
                })
                ->orWhereExists(function($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('ics_tbl')
                      ->whereColumn('ics_tbl.po_id_fk', 'po_tbl.po_id');
                })
                ->orWhereExists(function($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('par_tbl')
                      ->whereColumn('par_tbl.po_id_fk', 'po_tbl.po_id');
                })
                ->orWhereExists(function($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('rsmi_tbl')
                      ->whereColumn('rsmi_tbl.po_id_fk', 'po_tbl.po_id');
                })
                ->orWhereExists(function($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('rspi_tbl')
                      ->whereColumn('rspi_tbl.po_id_fk', 'po_tbl.po_id');
                })
                ->orWhereExists(function($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('ndr_tbl')
                      ->whereColumn('ndr_tbl.po_id_fk', 'po_tbl.po_id');
                });
            })
            ->whereNotExists(function($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('ndr_items_tbl')
                  ->whereColumn('ndr_items_tbl.ndr_po_items_id_fk', 'po_items_tbl.po_items_id');
            })
            ->sum('po_items_tbl.po_items_total');

        self::where('app_id', $appId)->update(['utilized_budget' => $utilizedBudget]);
    }
}

