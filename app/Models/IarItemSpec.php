<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IarItemSpec extends Model
{
    protected $table = 'iar_items_specs_tbl';
    public $timestamps = false;
    protected $primaryKey = 'iar_items_spec_id';

    protected $fillable = [
        'iar_items_id_fk',
        'po_items_spec_id_fk',
        'iar_spec_description',
    ];

    public function iarItem()
    {
        return $this->belongsTo(IarItem::class, 'iar_items_id_fk', 'iar_items_id');
    }

    public function poSpec()
    {
        return $this->belongsTo(PoSpec::class, 'po_items_spec_id_fk', 'po_items_spec_id');
    }
}
