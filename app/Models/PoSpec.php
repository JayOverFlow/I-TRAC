<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoSpec extends Model
{
    protected $table = 'po_items_specs_tbl';
    public $timestamps = false;
    protected $primaryKey = 'po_items_spec_id';

    protected $fillable = [
        'po_items_id_fk',
        'po_spec_description',
    ];

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'po_items_id_fk', 'po_items_id');
    }
}
