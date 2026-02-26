<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AppItem;

class AppParent extends Model
{
    protected $table = 'app_tbl';
    protected $primaryKey = 'app_id';

    protected $fillable = [
        'app_ppmp_items_id_fk',
        'app_status',
        'app_prepared_by_name',
        'app_prepared_by_designation',
        'app_recommending_by_name',
        'app_recommending_by_designation',
        'app_approved_by_name',
        'app_approved_by_designation',
        'app_dep_id_fk',
    ];

    public function appItems()
    {
        return $this->hasMany(AppItem::class, 'app_id_fk', 'app_id');
    }
}
