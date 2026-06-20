<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MrItemImage extends Model
{
    protected $table = 'mr_item_img_tbl';
    protected $primaryKey = 'img_id';

    protected $fillable = [
        'mr_id',
        'image_path',
    ];

    public function mr()
    {
        return $this->belongsTo(Mr::class, 'mr_id', 'mr_id');
    }
}
