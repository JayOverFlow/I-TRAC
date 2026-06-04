<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RsmiItem extends Model
{
    protected $table = 'rsmi_items_tbl';
    public $timestamps = false;
    protected $primaryKey = 'rsmi_items_id';

    protected $fillable = [
        'rsmi_id_fk',
        'rsmi_ris_no',
        'rsmi_center_code',
        'rsmi_stock_no',
        'rsmi_items_descrip',
        'rsmi_unit',
        'rsmi_quantity',
        'rsmi_unit_cost',
        'rsmi_amount',
        'recap_stock_no',
        'recap_quantity',
        'recap_unit_cost',
        'recap_total_cost',
        'recap_uacs_code',
    ];

    public function rsmi()
    {
        return $this->belongsTo(Rsmi::class, 'rsmi_id_fk', 'rsmi_id');
    }

    public function rsmiSpecs()
    {
        return $this->hasMany(RsmiItemSpec::class, 'rsmi_items_id_fk', 'rsmi_items_id');
    }
}
