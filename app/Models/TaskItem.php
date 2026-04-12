<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskItem extends Model
{
    protected $table = 'task_items_tbl';
    protected $primaryKey = 'task_item_id';
    public $timestamps = false;

    protected $fillable = [
        'task_id_fk',
        'app_item_id_fk'
    ];
}
