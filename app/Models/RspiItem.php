<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RspiItem extends Model
{
    protected $table = 'rspi_items_tbl';
    public $timestamps = false;
    protected $primaryKey = 'rspi_items_id';

    protected $fillable = [
        'rspi_id_fk',
        'rspi_ics_no',
        'rspi_center_code',
        'rspi_property_no',
        'rspi_items_descrip',
        'rspi_unit',
        'rspi_quantity',
        'rspi_unit_cost',
        'rspi_amount',
    ];

    public function rspi()
    {
        return $this->belongsTo(Rspi::class, 'rspi_id_fk', 'rspi_id');
    }

    public function rspiSpecs()
    {
        return $this->hasMany(RspiItemSpec::class, 'rspi_items_id_fk', 'rspi_items_id');
    }
}
