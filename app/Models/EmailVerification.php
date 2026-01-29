<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory; // For fake data generation
    
    protected $table = 'email_verifications'; // The table name
    protected $primaryKey = 'email_id'; // The PR of the table
    public $incrementing = true; // Optional cause Laravel automatically set it
    // NOTE: Use 'int' if causes an error
    protected $keyType = 'bigint'; // Tell the Laravel the datatype of the PK

    // Mass assignment fields
    protected $fillable = [
        'email',
        'verification_code',
        'expires_at',
    ];

    // Laravel casts datatypes
    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // METHODS
    // Get the active codes at the moment
    public function scopeGetActiveCodes($query) {
        return $query->where('expires_at', '>', now());
    }

    // Get the expired codes
    public function scopeGetExpiredCodes($query) {
        return $query->where('expires_at', '<=', now());
    }

    // Check if the code is expired
    public function isCodeExpired() {
        return $this->expires_at->isPast();
    }

    // Check if the code is valid
    public function isCodeValid() {
        return !$this->isCodeExpired();
    }
}
