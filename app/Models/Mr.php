<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mr extends Model
{
    protected $table = 'mr_tbl';
    public $timestamps = false;
    protected $primaryKey = 'mr_id';

    protected $fillable = [
        'assigned_to',
        'po_item_id_fk',
        'ris_item_id_fk',
        'par_item_id_fk',
        'mr_qr_code',
        'item_name',
        'specification',
        'quantity',
        'unit',
        'stock',
        'building',
        'room_no',
        'is_assigned',
        'date_scanned',
        'category',
        'status',
    ];

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'po_item_id_fk', 'po_items_id');
    }

    public function risItem()
    {
        return $this->belongsTo(RisItem::class, 'ris_item_id_fk', 'ris_items_id');
    }

    public function parItem()
    {
        return $this->belongsTo(ParItem::class, 'par_item_id_fk', 'par_items_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    public function images()
    {
        return $this->hasMany(MrItemImage::class, 'mr_id', 'mr_id');
    }
}
