<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDepartment extends Model
{
    protected $table = 'user_departments_tbl';
    protected $primaryKey = 'user_department_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id_fk',
        'department_id_fk',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id_fk', 'user_id');
    }
}
