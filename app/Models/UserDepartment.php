<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDepartment extends Model
{
    protected $table = 'user_departments_tbl';
    protected $primaryKey = 'user_department_id';

    protected $fillable = [
        'user_id_fk',
        'department_fk',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'user_id_fk', 'user_department_id');
    }
}
