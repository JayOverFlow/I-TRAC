<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdrItemSpec extends Model
{
    protected $table = 'ndr_items_specs_tbl';
    public $timestamps = false;
    protected $primaryKey = 'ndr_items_spec_id';

    protected $fillable = [
        'ndr_items_id_fk',
        'po_items_spec_id_fk',
        'ndr_spec_description',
    ];

    public function ndrItem()
    {
        return $this->belongsTo(NdrItem::class, 'ndr_items_id_fk', 'ndr_items_id');
    }

    public function poSpec()
    {
        return $this->belongsTo(PoSpec::class, 'po_items_spec_id_fk', 'po_items_spec_id');
    }
}
