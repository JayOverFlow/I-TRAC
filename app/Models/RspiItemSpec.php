<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RspiItemSpec extends Model
{
    protected $table = 'rspi_items_specs_tbl';
    public $timestamps = false;
    protected $primaryKey = 'rspi_items_spec_id';

    protected $fillable = [
        'rspi_items_id_fk',
        'po_items_spec_id_fk',
        'rspi_spec_description',
    ];

    public function rspiItem()
    {
        return $this->belongsTo(RspiItem::class, 'rspi_items_id_fk', 'rspi_items_id');
    }

    public function poSpec()
    {
        return $this->belongsTo(PoSpec::class, 'po_items_spec_id_fk', 'po_items_spec_id');
    }
}
