<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ris extends Model
{
    protected $table = 'ris_tbl';
    public $timestamps = false;
    protected $primaryKey = 'ris_id';

    protected $fillable = [
        'po_id_fk',
        'ris_fund_cluster',
        'ris_division',
        'ris_office',
        'ris_center_code',
        'ris_no',
        'ris_purpose',
        'ris_requested_by',
        'ris_requested_designation',
        'ris_requested_date',
        'ris_approved_by',
        'ris_approved_designation',
        'ris_approved_date',
        'ris_issued_by',
        'ris_issued_designation',
        'ris_issued_date',
        'ris_received_by',
        'ris_received_designation',
        'ris_received_date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PoParent::class, 'po_id_fk', 'po_id');
    }

    public function risItems()
    {
        return $this->hasMany(RisItem::class, 'ris_id_fk', 'ris_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'ris_requested_by', 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'ris_approved_by', 'user_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'ris_issued_by', 'user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'ris_received_by', 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'ris_office', 'dep_name');
    }
}
