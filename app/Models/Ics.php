<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ics extends Model
{
    protected $table = 'ics_tbl';
    public $timestamps = false;
    protected $primaryKey = 'ics_id';

    protected $fillable = [
        'po_id_fk',
        'ics_fund_cluster',
        'ics_po_no',
        'ics_no',
        'ics_code_no',
        'ics_received_from',
        'ics_received_from_pos',
        'ics_received_from_date',
        'ics_received_by',
        'ics_received_by_pos',
        'ics_received_by_date',
        'is_transfer',
        'mr_id_fk',
    ];

    public function mr()
    {
        return $this->belongsTo(Mr::class, 'mr_id_fk', 'mr_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PoParent::class, 'po_id_fk', 'po_id');
    }

    public function icsItems()
    {
        return $this->hasMany(IcsItem::class, 'ics_id_fk', 'ics_id');
    }

    public function giver()
    {
        return $this->belongsTo(User::class, 'ics_received_from', 'user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'ics_received_by', 'user_id');
    }
}
