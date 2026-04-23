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
    ];

    public function appItems()
    {
        return $this->hasMany(AppItem::class, 'app_id_fk', 'app_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'saved_by_user_id_fk', 'user_id');
    }
}
