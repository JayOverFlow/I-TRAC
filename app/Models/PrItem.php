<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrItem extends Model
{
    protected $table = 'pr_items_tbl';
    public $timestamps = false; // pr_items_tbl has no timestamp columns
    protected $primaryKey = 'pr_items_id';

    protected $fillable = [
        'pr_id_fk',
        'pr_app_item_id_fk',
        'pr_items_quantity',
        'pr_items_unit',
        'pr_items_cost',
        'pr_items_total_cost',
        'pr_items_descrip',
        'bidding_status',
        'pr_items_category'
    ];

    // Each item belongs to a PR parent
    public function prParent()
    {
        return $this->belongsTo(PrParent::class, 'pr_id_fk', 'pr_id');
    }

    // Each item can have many specifications
    public function prSpecs()
    {
        return $this->hasMany(PrSpec::class, 'pr_items_id_fk', 'pr_items_id');
    }
}
