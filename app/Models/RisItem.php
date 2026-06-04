<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RisItem extends Model
{
    protected $table = 'ris_items_tbl';
    public $timestamps = false;
    protected $primaryKey = 'ris_items_id';

    protected $fillable = [
        'ris_id_fk',
        'ris_po_items_id_fk',
        'ris_stock_no',
        'ris_unit',
        'ris_items_descrip',
        'ris_quantity',
        'ris_stock_available',
        'ris_issued_quantity',
        'ris_issued_remarks',
    ];

    public function ris()
    {
        return $this->belongsTo(Ris::class, 'ris_id_fk', 'ris_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'ris_po_items_id_fk', 'po_items_id');
    }

    public function risSpecs()
    {
        return $this->hasMany(RisItemSpec::class, 'ris_items_id_fk', 'ris_items_id');
    }
}
