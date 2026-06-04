<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iar extends Model
{
    protected $table = 'iar_tbl';
    public $timestamps = false;
    protected $primaryKey = 'iar_id';

    protected $fillable = [
        'iar_po_id_fk',
        'iar_fund_cluster',
        'iar_supplier',
        'iar_po_no',
        'iar_po_no_date',
        'iar_office',
        'iar_center_code',
        'iar_no',
        'iar_date',
        'iar_invoice_no',
        'iar_invoice_date',
        'iar_date_inspected',
        'iar_inspected_by',
        'iar_date_accepted',
        'iar_acceptance_type',
        'iar_accepted_by',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PoParent::class, 'iar_po_id_fk', 'po_id');
    }

    public function iarItems()
    {
        return $this->hasMany(IarItem::class, 'iar_id_fk', 'iar_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'iar_inspected_by', 'user_id');
    }

    public function acceptor()
    {
        return $this->belongsTo(User::class, 'iar_accepted_by', 'user_id');
    }
}
