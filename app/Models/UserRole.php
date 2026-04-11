<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'user_roles_tbl';
    protected $primaryKey = 'user_role_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id_fk',
        'role_id_fk',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id_fk', 'user_id');
    }

    public function role() {
        return $this->belongsTo(Role::class, 'role_id_fk', 'role_id');
    }
}
