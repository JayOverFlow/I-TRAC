<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;
    protected $table = 'activity_logs';
    protected $primaryKey = 'log_id';
    protected $fillable = [
        'log_admin_id',
        'log_action',
        'log_short_description',
        'log_full_description'
    ];
    public $timestamps = true;
    const CREATED_AT = 'log_created_at';
    const UPDATED_AT = null;


    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'log_admin_id', 'admin_id');
    }

    /**
     * Helper Method: Record a new admin activity log.
     * 
     * @param string $action           Category of the action (e.g., ROLE_UPDATE)
     * @param string $shortDescription Catchy summary for the header
     * @param string $fullDescription  Detailed audit trail for the logs page
     * @return ActivityLog
     */
    public static function log($action, $shortDescription, $fullDescription)
    {
        return self::create([
            'log_admin_id'          => session('admin_id'), // Automatically grab from session
            'log_action'            => $action,
            'log_short_description' => $shortDescription,
            'log_full_description'  => $fullDescription,
        ]);
    }
}
