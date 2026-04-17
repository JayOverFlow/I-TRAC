<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrSpec extends Model
{
    protected $table = 'pr_items_specs_tbl';
    public $timestamps = false; // pr_items_specs_tbl has no timestamp columns
    protected $primaryKey = 'pr_items_spec_id';

    protected $fillable = [
        'pr_items_id_fk',
        'pr_spec_spec',
    ];

    // Each spec belongs to one item
    public function prItem()
    {
        return $this->belongsTo(PrItem::class, 'pr_items_id_fk', 'pr_items_id');
    }
}
