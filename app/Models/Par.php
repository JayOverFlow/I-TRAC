<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Par extends Model
{
    protected $table = 'par_tbl';
    public $timestamps = false;
    protected $primaryKey = 'par_id';

    protected $fillable = [
        'po_id_fk',
        'par_fund_cluster',
        'par_po_no',
        'par_no',
        'par_code',
        'par_received_by',
        'par_received_by_pos',
        'par_received_by_date',
        'par_issued_by',
        'par_issued_by_pos',
        'par_issued_by_date',
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

    public function parItems()
    {
        return $this->hasMany(ParItem::class, 'par_id_fk', 'par_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'par_received_by', 'user_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'par_issued_by', 'user_id');
    }
}
