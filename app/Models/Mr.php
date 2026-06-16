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
        'mr_qr_code',
        'item_name',
        'specification',
        'quantity',
        'unit',
        'stock',
        'location',
        'item_image',
        'is_assigned',
        'date_scanned',
    ];

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'po_item_id_fk', 'po_items_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }
}
