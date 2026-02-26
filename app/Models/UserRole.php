<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'user_roles_tbl';
    protected $primaryKey = 'user_role_id';

    protected $fillable = [
        'user_id_fk',
        'role_id_fk',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id_fk', 'id');
    }
    public function userRole() {
        return $this->belongsTo(UserRole::class, 'role_id_fk', 'id');
    }
}
