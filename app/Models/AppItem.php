<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AppParent;

class AppItem extends Model
{
    protected $table = 'app_items_tbl';
    protected $primaryKey = 'app_item_id';

    protected $fillable = [
        'app_id_fk',
        'app_item_name',
        'app_item_pmo',
        'app_item_mode',
        'app_item_estimated_total',
        'app_item_estimated_mooe',
        'app_item_estimated_co',
        'app_item_adspost',
        'app_item_subopen',
        'app_item_notice',
        'app_item_contract',
        'app_item_source_fund',
        'app_item_code',
    ];

    public function app()
    {
        return $this->belongsTo(AppParent::class, 'app_id_fk', 'app_id');
    }
}
