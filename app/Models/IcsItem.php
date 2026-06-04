<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IcsItem extends Model
{
    protected $table = 'ics_items_tbl';
    public $timestamps = false;
    protected $primaryKey = 'ics_items_id';

    protected $fillable = [
        'ics_id_fk',
        'ics_quantity',
        'ics_unit',
        'ics_unit_cost',
        'ics_total_cost',
        'ics_items_descrip',
        'ics_inventory_item_no',
        'ics_estimated_useful_life',
    ];

    public function ics()
    {
        return $this->belongsTo(Ics::class, 'ics_id_fk', 'ics_id');
    }

    public function icsSpecs()
    {
        return $this->hasMany(IcsItemSpec::class, 'ics_items_id_fk', 'ics_items_id');
    }
}
