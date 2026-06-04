<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdrItem extends Model
{
    protected $table = 'ndr_items_tbl';
    public $timestamps = false;
    protected $primaryKey = 'ndr_items_id';

    protected $fillable = [
        'ndr_id_fk',
        'ndr_po_items_id_fk',
        'ndr_stock_no',
        'ndr_unit',
        'ndr_items_descrip',
        'ndr_quantity',
    ];

    public function ndr()
    {
        return $this->belongsTo(Ndr::class, 'ndr_id_fk', 'ndr_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'ndr_po_items_id_fk', 'po_items_id');
    }

    public function ndrSpecs()
    {
        return $this->hasMany(NdrItemSpec::class, 'ndr_items_id_fk', 'ndr_items_id');
    }
}
