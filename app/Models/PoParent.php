<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoParent extends Model
{
    protected $table = 'po_tbl';
    public $timestamps = false;
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'pr_id_fk',
        'po_title',
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
        'po_status',
        'retrieved_by',
        'is_da_exported',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PrParent::class, 'pr_id_fk', 'pr_id');
    }

    public function savedBy()
    {
        return $this->belongsTo(User::class, 'saved_by_user_id_fk', 'user_id');
    }

    public function poItems()
    {
        return $this->hasMany(PoItem::class, 'po_id_fk', 'po_id');
    }

    public function retriever()
    {
        return $this->belongsTo(User::class, 'retrieved_by', 'user_id');
    }

    public function iarReports()
    {
        return $this->hasMany(Iar::class, 'iar_po_id_fk', 'po_id');
    }

    public function risSlips()
    {
        return $this->hasMany(Ris::class, 'po_id_fk', 'po_id');
    }

    public function rsmiReports()
    {
        return $this->hasMany(Rsmi::class, 'po_id_fk', 'po_id');
    }

    public function icsSlips()
    {
        return $this->hasMany(Ics::class, 'po_id_fk', 'po_id');
    }

    public function rspiReports()
    {
        return $this->hasMany(Rspi::class, 'po_id_fk', 'po_id');
    }

    public function parReceipts()
    {
        return $this->hasMany(Par::class, 'po_id_fk', 'po_id');
    }

    public function ndrReports()
    {
        return $this->hasMany(Ndr::class, 'po_id_fk', 'po_id');
    }

    /**
     * Check if the Purchase Order has any delivery attachments.
     * Checks loaded relations to prevent N+1 query issues.
     *
     * @return bool
     */
    public function hasDeliveryAttachment()
    {
        return ($this->relationLoaded('iarReports') ? $this->iarReports->isNotEmpty() : $this->iarReports()->exists()) ||
               ($this->relationLoaded('risSlips') ? $this->risSlips->isNotEmpty() : $this->risSlips()->exists()) ||
               ($this->relationLoaded('icsSlips') ? $this->icsSlips->isNotEmpty() : $this->icsSlips()->exists()) ||
               ($this->relationLoaded('parReceipts') ? $this->parReceipts->isNotEmpty() : $this->parReceipts()->exists()) ||
               ($this->relationLoaded('rsmiReports') ? $this->rsmiReports->isNotEmpty() : $this->rsmiReports()->exists()) ||
               ($this->relationLoaded('rspiReports') ? $this->rspiReports->isNotEmpty() : $this->rspiReports()->exists());
    }

    public function hasAnyDaExported()
    {
        $this->load(['iarReports', 'risSlips', 'icsSlips', 'parReceipts', 'rsmiReports', 'rspiReports']);

        $das = collect()
            ->concat($this->iarReports)
            ->concat($this->risSlips)
            ->concat($this->icsSlips)
            ->concat($this->parReceipts)
            ->concat($this->rsmiReports)
            ->concat($this->rspiReports);

        return $das->contains('is_exported', 1);
    }

    public function checkAndSetDaExportStatus()
    {
        $this->load(['iarReports', 'risSlips', 'icsSlips', 'parReceipts', 'rsmiReports', 'rspiReports']);

        $das = collect()
            ->concat($this->iarReports)
            ->concat($this->risSlips)
            ->concat($this->icsSlips)
            ->concat($this->parReceipts)
            ->concat($this->rsmiReports)
            ->concat($this->rspiReports);

        if ($das->isEmpty()) {
            return;
        }

        $allExported = $das->every(fn($da) => $da->is_exported == 1);

        if ($allExported) {
            $this->update(['is_da_exported' => 1]);

            $pr = $this->purchaseRequest;
            if ($pr) {
                $pr->load('purchaseOrders');
                $allPosExported = $pr->purchaseOrders->every(fn($po) => $po->is_da_exported == 1);
                if ($allPosExported) {
                    $pr->update(['da_exported_at' => now()]);
                }
            }
        }
    }
}
