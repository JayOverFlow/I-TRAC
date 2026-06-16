<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoItem extends Model
{
    protected $table = 'po_items_tbl';
    public $timestamps = false;
    protected $primaryKey = 'po_items_id';

    protected $fillable = [
        'po_id_fk',
        'po_pr_items_id_fk',
        'po_items_stockno',
        'po_items_unit',
        'po_items_descrip',
        'po_items_quantity',
        'po_items_cost',
        'po_items_amount',
        'po_items_total',
        'po_items_category',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PoParent::class, 'po_id_fk', 'po_id');
    }

    public function prItem()
    {
        return $this->belongsTo(PrItem::class, 'po_pr_items_id_fk', 'pr_items_id');
    }

    public function poSpecs()
    {
        return $this->hasMany(PoSpec::class, 'po_items_id_fk', 'po_items_id');
    }

    public function mrs()
    {
        return $this->hasMany(Mr::class, 'po_item_id_fk', 'po_items_id');
    }

    public function parItems()
    {
        return $this->hasMany(ParItem::class, 'par_po_items_id_fk', 'po_items_id');
    }
}
