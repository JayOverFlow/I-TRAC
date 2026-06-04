<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IarItem extends Model
{
    protected $table = 'iar_items_tbl';
    public $timestamps = false;
    protected $primaryKey = 'iar_items_id';

    protected $fillable = [
        'iar_id_fk',
        'iar_po_items_id_fk',
        'iar_stock_no',
        'iar_items_descrip',
        'iar_unit',
        'iar_quantity',
    ];

    public function iar()
    {
        return $this->belongsTo(Iar::class, 'iar_id_fk', 'iar_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'iar_po_items_id_fk', 'po_items_id');
    }

    public function iarSpecs()
    {
        return $this->hasMany(IarItemSpec::class, 'iar_items_id_fk', 'iar_items_id');
    }
}
