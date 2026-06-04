<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rspi extends Model
{
    protected $table = 'rspi_tbl';
    public $timestamps = false;
    protected $primaryKey = 'rspi_id';

    protected $fillable = [
        'po_id_fk',
        'rspi_fund_cluster',
        'rspi_po_no',
        'rspi_serial_no',
        'rspi_date',
        'rspi_user_id_fk',
        'rspi_designation',
        'rspi_posted_by',
        'rspi_posted_date',
        'rspi_total',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PoParent::class, 'po_id_fk', 'po_id');
    }

    public function rspiItems()
    {
        return $this->hasMany(RspiItem::class, 'rspi_id_fk', 'rspi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'rspi_user_id_fk', 'user_id');
    }
}
