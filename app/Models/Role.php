<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles_tbl';
    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
        'role_dep_id_fk',
        'role_parent_id',
        'gen_role',
    ];

    public function user() {
        return $this->hasOne(UserRole::class, 'role_id_fk', 'role_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'role_dep_id_fk', 'dep_id');
    }
}
