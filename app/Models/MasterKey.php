<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKey extends Model
{
    use HasFactory;

    protected $table = 'master_keys_tbl';
    protected $primaryKey = 'master_key_id';
    protected $fillable = [
        'master_key'
    ];

    public $timestamps = false;

    /**
     * Get the admins for the master key.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'admin_key', 'master_key_id');
    }

    /**
     * Check if the master key is already used by an admin
     */
    public function isUsed(): bool
    {
        return $this->admins()->exists();
    }
}
