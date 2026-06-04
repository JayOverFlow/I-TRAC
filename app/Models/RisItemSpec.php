<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RisItemSpec extends Model
{
    protected $table = 'ris_items_specs_tbl';
    public $timestamps = false;
    protected $primaryKey = 'ris_items_spec_id';

    protected $fillable = [
        'ris_items_id_fk',
        'po_items_spec_id_fk',
        'ris_spec_description',
    ];

    public function risItem()
    {
        return $this->belongsTo(RisItem::class, 'ris_items_id_fk', 'ris_items_id');
    }

    public function poSpec()
    {
        return $this->belongsTo(PoSpec::class, 'po_items_spec_id_fk', 'po_items_spec_id');
    }
}
