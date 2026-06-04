<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IcsItemSpec extends Model
{
    protected $table = 'ics_items_specs_tbl';
    public $timestamps = false;
    protected $primaryKey = 'ics_items_spec_id';

    protected $fillable = [
        'ics_items_id_fk',
        'po_items_spec_id_fk',
        'ics_spec_description',
    ];

    public function icsItem()
    {
        return $this->belongsTo(IcsItem::class, 'ics_items_id_fk', 'ics_items_id');
    }

    public function poSpec()
    {
        return $this->belongsTo(PoSpec::class, 'po_items_spec_id_fk', 'po_items_spec_id');
    }
}
