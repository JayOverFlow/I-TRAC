<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParItem extends Model
{
    protected $table = 'par_items_tbl';
    public $timestamps = false;
    protected $primaryKey = 'par_items_id';

    protected $fillable = [
        'par_id_fk',
        'par_po_items_id_fk',
        'par_quantity',
        'par_unit',
        'par_items_descrip',
        'par_property_no',
        'par_date_acquired',
        'par_amount',
    ];

    public function par()
    {
        return $this->belongsTo(Par::class, 'par_id_fk', 'par_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'par_po_items_id_fk', 'po_items_id');
    }

    public function parSpecs()
    {
        return $this->hasMany(ParItemSpec::class, 'par_items_id_fk', 'par_items_id');
    }
}
