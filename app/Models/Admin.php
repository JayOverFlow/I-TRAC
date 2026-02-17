<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admins_tbl';
    protected $primaryKey = 'admin_id';
    protected $fillable = [
        'admin_username',
        'admin_password',
        'admin_key'
    ];

    public $timestamps = false;

    /**
     * Get the master key that owns the admin.
     */
    public function masterKey(): BelongsTo
    {
        return $this->belongsTo(MasterKey::class, 'admin_key', 'master_key_id');
    }
}
