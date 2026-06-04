<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RsmiItemSpec extends Model
{
    protected $table = 'rsmi_items_specs_tbl';
    public $timestamps = false;
    protected $primaryKey = 'rsmi_items_spec_id';

    protected $fillable = [
        'rsmi_items_id_fk',
        'po_items_spec_id_fk',
        'rsmi_spec_description',
    ];

    public function rsmiItem()
    {
        return $this->belongsTo(RsmiItem::class, 'rsmi_items_id_fk', 'rsmi_items_id');
    }

    public function poSpec()
    {
        return $this->belongsTo(PoSpec::class, 'po_items_spec_id_fk', 'po_items_spec_id');
    }
}
