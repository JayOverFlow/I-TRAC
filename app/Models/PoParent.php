<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoParent extends Model
{
    protected $table = 'po_tbl';
    public $timestamps = false;
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'po_pr_id_fk',
        'po_supplier',
        'po_no',
        'po_date',
        'po_address',
        'po_tele',
        'po_tin',
        'po_mode',
        'po_tuptin',
        'po_place_delivery',
        'po_delivery_term',
        'po_date_delivery',
        'po_payment_term',
        'po_signed_by_fk',
        'po_fund_cluster',
        'po_fund_available',
        'po_orsburs',
        'po_date_orsburs',
        'po_amount',
        'po_total_amount',
        'po_amount_in_words',
        'po_description',
        'po_remarks',
        'conforme_name_of_supplier',
        'conforme_date',
        'conforme_campus_director',
        'saved_by_user_id_fk',
        'po_unique_code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function poItems()
    {
        return $this->hasMany(PoItem::class, 'po_id_fk', 'po_id');
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PrParent::class, 'po_pr_id_fk', 'pr_id');
    }

    public function savedBy()
    {
        return $this->belongsTo(User::class, 'saved_by_user_id_fk', 'user_id');
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'po_signed_by_fk', 'user_id');
    }
}
