<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AppParent;

class AppItem extends Model
{
    protected $table = 'app_items_tbl';
    protected $primaryKey = 'app_item_id';
    public $timestamps = false;

    protected $fillable = [
        'app_id_fk',
        'app_item_proj_title',
        'app_items_end_user',
        'app_items_gen_desc',
        'app_items_mode',
        'app_items_covered',
        'app_items_criteria',
        'app_items_start',
        'app_items_end',
        'app_items_source',
        'app_items_esti_budget',
        'app_items_tools',
        'app_items_remarks',
        'app_items_total',
        'app_items_is_assigned',
    ];


    public function app()
    {
        return $this->belongsTo(AppParent::class, 'app_id_fk', 'app_id');
    }
}
