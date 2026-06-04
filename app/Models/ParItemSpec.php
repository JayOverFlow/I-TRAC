<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParItemSpec extends Model
{
    protected $table = 'par_items_specs_tbl';
    public $timestamps = false;
    protected $primaryKey = 'par_items_spec_id';

    protected $fillable = [
        'par_items_id_fk',
        'po_items_spec_id_fk',
        'par_spec_description',
    ];

    public function parItem()
    {
        return $this->belongsTo(ParItem::class, 'par_items_id_fk', 'par_items_id');
    }

    public function poSpec()
    {
        return $this->belongsTo(PoSpec::class, 'po_items_spec_id_fk', 'po_items_spec_id');
    }
}
