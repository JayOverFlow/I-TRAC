<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ndr extends Model
{
    protected $table = 'ndr_tbl';
    public $timestamps = false;
    protected $primaryKey = 'ndr_id';

    protected $fillable = [
        'po_id_fk',
        'ndr_no',
        'ndr_date',
        'ndr_reported_by',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PoParent::class, 'po_id_fk', 'po_id');
    }

    public function ndrItems()
    {
        return $this->hasMany(NdrItem::class, 'ndr_id_fk', 'ndr_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'ndr_reported_by', 'user_id');
    }
}
