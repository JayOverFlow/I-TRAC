<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rsmi extends Model
{
    protected $table = 'rsmi_tbl';
    public $timestamps = false;
    protected $primaryKey = 'rsmi_id';

    protected $fillable = [
        'po_id_fk',
        'rsmi_fund_cluster',
        'rsmi_po_no',
        'rsmi_serial_no',
        'rsmi_date',
        'rsmi_user_id_fk',
        'rsmi_designation',
        'rsmi_posted_by',
        'rsmi_posted_date',
        'rsmi_total',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PoParent::class, 'po_id_fk', 'po_id');
    }

    public function rsmiItems()
    {
        return $this->hasMany(RsmiItem::class, 'rsmi_id_fk', 'rsmi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'rsmi_user_id_fk', 'user_id');
    }
}
